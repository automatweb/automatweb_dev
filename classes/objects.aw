<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/objects.aw,v 2.46 2003/04/23 12:05:24 duke Exp $
// objects.aw - objektide haldamisega seotud funktsioonid
class db_objects extends aw_template 
{
	function db_objects() 
	{
		$this->init("");
		$this->lc_load("objects","lc_objects");
		lc_load("definition");
	}

	////
	// !Genereerib mingit klassi objektide nimekirja, rekursiivselt alates $start_from-ist
	// Eeliseks järgneva funktsiooni ees on see, et ei loeta koiki menüüsid sisse
	// see versioon ei prindi objektide nimekirja v2lja ka. mix seda yldse vaja oli printida?!?
	// ja tagastatav array on kujul array($oid => $row)
	function gen_rec_list_noprint($args = array())
	{
		extract($args);
		// vaatame ainult seda tüüpi objekte
		$this->class_id = 1;
		$this->spacer = 0;
		// moodustame 2mootmelise array koigist objektidest
		// parent -> child1,(child2,...childn)
		$this->rec_list = array(); // siia satuvad koik need objektid
		$this->no_parent_rel = true;
		$this->_gen_rec_list(array("$start_from"));
		return $this->rec_list;
	}

	////
	// !Rekursiivne funktsioon, kutsutakse välja gen_rec_list seest
	function _gen_rec_list($parents = array())
	{
		$this->save_handle();
		$plist = join(",",$parents);
		if($plist == "")
		{
			$this->restore_handle();
			return;
		}
		$q = sprintf("SELECT * FROM objects WHERE class_id = '%d' AND parent IN (%s)",
				$this->class_id,
				$plist);
		$this->db_query($q);
		$_parents = array();
		while($row = $this->db_next())
		{
			$_parents[] = $row["oid"];
			if ($this->no_parent_rel)
			{
				$this->rec_list[$row["oid"]] = $row;
			}
			else
			{
				$this->rec_list[$row["parent"]][$row["oid"]] = $row;
			}
		};
		if (sizeof($_parents) > 0)
		{
			$this->_gen_rec_list($_parents);
		};
		$this->restore_handle();
	}

	function orb_get_list($arr)
	{
		extract($arr);
		if (!isset($rootobj))
		{
			$rootobj = -1;
		}
		$ret = $this->get_menu_list($ignore_langmenus,$empty,$rootobj);
		return $ret;
	}

	function get_list($ignore_langmenus = false,$empty = false,$rootobj = -1) 
	{
		return $this->get_menu_list($ignore_langmenus,$empty,$rootobj);
	}
	
	function count_by_parent($parent,$typearr = "") 
	{
		if (is_array($typearr))
		{
			$typestr = "AND class_id IN (".join(",",$typearr).") ";
		}
		else
		{
			$typestr = "";
		}
		$q = "SELECT count(*) as cnt
			FROM objects
			WHERE parent = '$parent' $typestr";
		return $this->db_fetch_field($q,"cnt");
	}

	function listall_types($parent,$typearr)
	{
		$tstr = join(",", $typearr);
		$this->db_query("SELECT * FROM objects WHERE parent = $parent AND class_id IN ($tstr) ");
	}
};


class objects extends db_objects
{
	function objects()
	{
		$this->db_objects();
	}

	////
	// !Object search
	// otype(int) - allow to search only for a single object type
	// one(int) - use picker template (search_one) 
	function search($arr)
	{
		$s = get_instance("search");
		// all the required fields and their default values
		$defaults = array(
			"name" => "",
			"comment" => "",
			"class_id" => 0,
			"parent" => 0,
			"createdby" => "",
			"modifiedby" => "",
			"active" => 1,
			"alias" => "",
		);

		// now override the defaults with possible values from the user
		$real_fields = array_merge($defaults,$arr);

		return $s->show($real_fields);
		$this->tpl_init("automatweb/objects");
		$this->sub_merge = 1;
		extract($arr);

		// search types:
		// 0 or not set	: the usual object search
		// 1 : called from messenger to search for objects to attach
		// the only difference between right now is a different template
		if ($arr["stype"] == 1)
		{
			$this->read_template("search_messenger.tpl");
			// target specifies the message to which the objects should be attached
			$this->target = $arr["target"];
		}
		else
		{
			if ($one)
			{
				$prnt_url = $this->mk_my_orb("login_menus",array(),"config");
				$this->mk_path(0,"<a href='$prnt_url'>Action menüüd</a> / Vali menüü");
				$tpl = "search_one.tpl";
			}
			else
			{
				$tpl = "search.tpl";
			};

			$this->read_template($tpl);
		};

		// FIXME: loeb globaalsest skoobist parameetreid (NB! need on arrayd)
		global $s;

		$SITE_ID = $this->cfg["site_id"];

		$numfields = array("parent" => 1,"class_id" => 1,"created" => 1,"modified" => 1 ,"status" => 1);
		$found = false;
		$se = array();
		if (is_array($s))
		{
			reset($s);
			while (list($k,$v) = each($s))
			{
				if ($v != "" || ($numfields[$k] && $v > 0))
				{
					$found = true;
					if ($k == "active" && $v == 1)
					{
						$se[] = " objects.status = 2 ";
					}
					else
					if ($numfields[$k])
					{
						if ($v > 0)
						{
							$se[] = " objects.$k = '".$v."' ";
						}
					}
					else
					{
						if ($v != "%")
						{
							$se[] = " objects.$k LIKE '%".$v."%' ";
						}
					}
				}
			}
		}

		if ($found)
		{
			$ses = join("AND",$se);
			if ($ses != "")
			{
				$ses="AND ".$ses;
			}
			$q = "SELECT * FROM objects WHERE objects.status != 0 AND (objects.site_id = $SITE_ID OR objects.site_id IS NULL) $ses";
			// XXX: this does not find menus which are less than 3 levels deep.
			//$q = "SELECT objects.*,o_p.name as parent_name,o_p_p.name as parent_parent_name,o_p_p_p.name as parent_parent_parent_name FROM objects, objects as o_p,objects as o_p_p,objects as o_p_p_p  WHERE o_p.oid = objects.parent AND o_p_p.oid = o_p.parent AND o_p_p_p.oid = o_p_p.parent AND objects.status != 0 AND (objects.site_id = $SITE_ID OR objects.site_id IS NULL) $ses";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"name" => $row["name"], 
					"type"	=> $this->cfg["classes"][$row["class_id"]]["name"],
					"change" => $this->mk_orb("change", array("id" => $row["oid"], "parent" => $row["parent"]), $this->cfg["classes"][$row["class_id"]]["file"]),
					"created" => $this->time2date($row["created"], 2),
					"modified" => $this->time2date($row["modified"], 2),
					"createdby" => $row["createdby"],
					"modifiedby" => $row["modifiedby"],
					"parent_name" => $row["parent_name"],
					"oid" => $row["oid"],
					"class_id" => $row["class_id"],
					"pick" => urldecode($return_url) . "&pick=$row[oid]",
					"parent_parent_name" => $row["parent_parent_name"],
					"parent_parent_parent_name" => $row["parent_parent_parent_name"]
				));
				$l.=$this->parse("LINE");
			}
		
			$ob = get_instance("objects");


			$this->vars(array(
				"LINE" => $l,
				"moveto" => $this->picker(0,$ob->get_list(false,true)),
			));
			$this->parse("FOUND");
		}
		else
		{
			$s["name"] = "%";
			$s["comment"] = "%";
			$s["type"] = 0;
		}

		if ($otype)
		{
			$tar[$otype] = $this->cfg["classes"][$otype]["name"];
		}
		else
		{
			$tar = array(0 => " " . LC_OBJECTS_ALL);
			reset($this->cfg["classes"]);
			while (list($v,) = each($this->cfg["classes"]))
			{
				$tar[$v] = $this->cfg["classes"][$v]["name"];
			}
		};

		// sort type list by name
		asort($tar);

		$u = get_instance("users");
		$uids = $u->listall_acl();
		$uids[""] = "";


		$li = $this->get_list(false,true);
		//$li[1] = "Root";
		$this->vars(array(
			"s_name"	=> $s["name"],
			"s_comment"	=> $s["comment"],
			"types"	=> $this->picker($s["class_id"], $tar),
			"parents" => $this->picker($s["parent"],$li),
			"createdby" => $this->picker($s["createdby"],$uids),
			"modifiedby" => $this->picker($s["modifiedby"],$uids),
			"active"	=> checked($s["active"]),
			"alias"		=> $s["alias"],
			"reforb" => $this->mk_reforb("search_submit", array("stype" => $stype,"target" => $target,"otype" => $otype,"one" => $one,"return_url" => urlencode($return_url))),
		));
		return $this->parse();
	}

	function search_submit($arr)
	{
		extract($arr);

		if ($attach)
		{
			$this->_attach_objects($arr);
			//print "<script>window.close();</script>";
			exit;
		};

		$updmenus = array();
		// Renaming ...
		if (is_array($old_text))
		{
			foreach($old_text as $oid => $name)
			{
				if ($text[$oid] != $name)
				{
					$this->upd_object(array("oid" => $oid, "name" => $text[$oid]));
					
					// form elements get special handling
					if ($class_id[$oid] == CL_FORM_ELEMENT)
					{
						$this->_search_rename_form_element($oid,$name);
					}
					else
					if ($class_id[$oid] == CL_PSEUDO)
					{
						$updmenus[] = $oid;
					}
				}
			}
		}

		// ..moving..
		if (is_array($sel) && $moveto != 0)
		{
			foreach($sel as $oid => $one)
			{
				$this->upd_object(array("oid" => $oid, "parent" => $moveto));
				if ($class_id[$oid] == CL_PSEUDO)
				{
					$updmenus[] = $oid;
				}
			}
		}

		// ..and deleting..
		if (is_array($sel) && $delete != "")
		{
			foreach($sel as $oid => $one)
			{
				if ($one == 1)
				{
					$this->delete_object($oid);
					if ($class_id[$oid] == CL_PSEUDO)
					{
						$updmenus[] = $oid;
					}
				}
			}
		}

		// just in case if any menus were deleted or changed - I thought it would be too expensive to put checks in 
		// core::delete_object and core::upd_object to check if menus were changed and then flush the cache 
		// - yeah, that would be a lot safer, but is it really necessary?
		$m = get_instance("menuedit");
		$m->invalidate_menu_cache($updmenus);

		return $this->mk_my_orb("search", array("s[name]" => $s["name"],"s[comment]" => $s["comment"],"s[class_id]" => $s["class_id"],"s[parent]" => $s["parent"],"s[createdby]" => $s["createdby"], "s[modifiedby]" => $s["modifiedby"], "s[active]" => $s["active"], "s[alias]" => $s["alias"],"stype" => $stype,"target" => $target,"otype" => $otype,"one" => $one,"return_url" => urlencode($return_url)));
	}
						
	function _search_rename_form_element($oid,$name)
	{
		$this->db_query("SELECT * FROM element2form WHERE el_id = ".$oid);
		while ($drow = $this->db_next())
		{
			$fup = get_instance("formgen/form");
			$fup->load($drow["form_id"]);
			for ($row = 0;$row < $fup->arr["rows"]; $row++)
			{
				for ($col = 0; $col < $fup->arr["cols"]; $col++)
				{
					if (is_array($fup->arr["elements"][$row][$col]))
					{
						foreach($fup->arr["elements"][$row][$col] as $k => $v)
						{
							if ($k == $oid)
							{
								$fup->arr["elements"][$row][$col][$k]["name"] = $name;
							}
						}
					}
				}
			}
			$fup->save();
		}
	}

	function _attach_objects($args = array())
	{
		extract($args);
		// sel nimelises arrays on meil objektide ID-d, mida siis tuleks, saaks, vmt attachida
		// target sisaldab kirja id-d, mille kylge objekt attachida tuleb
		if (is_array($sel))
		{
			$awf = get_instance("file");
			foreach($sel as $key => $val)
			{
				$obj = $this->get_object($key);
				if ($obj["class_id"] == CL_FILE)
				{
					// those get special handling
					$awf->cp(array("id" => $key,"parent" => $target));
				}
				else
				{
					$html = $this->show(array("id" => $key));
					$awf->put(array(
						"type" => "text/html",
						"content" => $html,
						"parent" => $target,
						// extension is for that stupid browser, that ignores mime types
						// and looks only at the end of the filename. 
						"filename" => $this->name . ".html",
					));
				};
			};
		};

		// save[0], because the composer windows has two buttons with that name and therefore
		// we cannot refer to it as just "save"
		print "<script>window.opener.document.writemessage.save[0].click(); window.close();</script>";
			
	}


	////
	// !Displays an object. Any object.
	// and yes, it's not very smart. all the functionality to generate a preview of an object
	// should be inside the correspondending class
	function show($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		if (not($obj))
		{
			return false;
		};

		$this->name = strip_tags($obj["name"]);
		$this->has_output = true;
		// shouldn't we check ACL here?
		switch($obj["class_id"])
		{
			case CL_EXTLINK:
				$t = get_instance("extlinks");
				list($url,$target,$caption) = $t->draw_link($obj["oid"]);
				$replacement = sprintf("<a href='%s' %s>%s</a>",$url,$target,$caption);
				break;

			case CL_IMAGE:
				$t = get_instance("image");
				$idata = $t->get_image_by_id($obj["oid"]);
				$replacement = sprintf("<img src='%s'><br>%s",$idata["url"],$idata["comment"]);
				break;
			case CL_TABLE:
				$t = get_instance("table");
				$replacement = $t->show(array("id" => $obj["oid"],"align" => $align));
				break;

			case CL_FORM_ENTRY:
				$t = get_instance("formgen/form");
				$frm = $t->get_form_for_entry($obj["oid"]);
				$ops = $t->get_op_list($frm);
				list($x,$y) = each($ops);
				if (is_array($y))
				{
					list($id,$name) = each($y);
				};

				$replacement = $t->show(array(
					"id" => $frm,
					"entry_id" => $obj["oid"],
					"op_id" => $id,
				));
				break;

			case CL_FORM_OUTPUT:
				$t = get_instance("formgen/form");
				$frm = $t->get_op_forms($obj["oid"]);
				$x = $y = 0;
				if (is_array($frm))
				{
					list($x,$y) = each($frm);
				};
				
				$replacement = $t->show(array(
					"id" => $x,
					"op_id" => $id,
				));

				break;

			
			case CL_FORM:
				$t = get_instance("formgen/form");
				$replacement = $t->gen_preview(array(
					"id" => $obj["oid"],
					"form_action" => "/reforb.".$this->cfg["ext"],
				));
				break;
			
			case CL_FORM_CHAIN:
				$t = get_instance("formgen/form_chain");
				$replacement = $t->show(array(
					"id" => $obj["oid"],
				));
				break;

			case CL_GRAPH:
				$replacement = "<img src='".$this->mk_my_orb("show", array("id" => $obj["oid"]),"graph",false,true)."'>";
				break;

			case CL_GALLERY:
				$t = get_instance("gallery");
				$t->load($obj["oid"],$GLOBALS["page"]);
				$replacement = $t->show($GLOBALS["page"]);
				break;

			case CL_FILE:
				$t = get_instance("file");
				$fi = $t->get_file_by_id($obj["oid"]);
				if ($fi["showal"] == 1)
				{
					// n2itame kohe
					// kontrollime koigepealta, kas headerid on ehk väljastatud juba.
					// dokumendi preview vaatamisel ntx on.
					if ($fi["type"] == "text/html")
					{
						if (!headers_sent())
						{
							header("Content-type: text/html");
						};

						$replacement = $fi["content"];
					}
					else
					{
						header("Content-type: ".$fi["type"]);
						die($fi["content"]);
					}
				}
				else
				{
					if ($fi["newwindow"])
					{
						$ss = "target=\"_new\"";
					}

					$comment = $fi["comment"];
					if ($comment == "")
					{
						$comment = $fi["name"];
					}

					classload("file");
					$replacement = "<a $ss class=\"sisutekst\" href='".file::get_url($obj["oid"],$fi["name"])."'>$comment</a>";
				}	
				break;


			case CL_DOCUMENT:
				$t = get_instance("document");
				$replacement = $t->gen_preview(array("docid" => $obj["oid"]));
				break;

			case CL_PSEUDO:
				$replacement = "<a href='/index.".$this->cfg["ext"]."?section=$obj[oid]'>$obj[name]</a>";
				break;

			case CL_CALENDAR:
				$cal = get_instance("planner");
				$cform = $args["form"];
				$ctrl = 0;

				// chain entry id.
				$ceid = $cform->current_chain_entry;

				if ($cform && $cform->arr["has_calendar"] && $cform->arr["cal_controller"])
				{
					$ctrl = $cform->id;
				};

				//$curl = $this->mk_my_orb("view",array("type" => "week","id" => $obj["oid"],"ctrl" => $ctrl,"ctrle" => $ceid,"chain_id" => $cform->id),"planner",false,true);
				$curl = $this->mk_my_orb("view",array("type" => "week","id" => $ceid),"planner",false,true);
				if (not($caption))
				{
					$caption = "View calendar";
				};
				$replacement = "<a target='new' href='$curl'>$caption</a>";
				/*
				$replacement = $cal->view(array("id" => $obj["oid"],"type" => "week"));
				*/
				break;

			default:
				$this->has_output = false;
				$replacement = $obj["class_id"] . " This object class has no output yet<br>";
		}
		return $replacement;

	}

	function get_fvalue($args = array())
	{
		extract($args);
		switch($_keyname)
		{
			case "name":
				$retval = ($this->values["name"]) ? $this->values["name"] : "%";
				break;

			case "comment":
				$retval = ($this->values["comment"]) ? $this->values["comment"] : "%";
				break;

			case "type":
				$tar = array();
				if ($otype)
				{
					$tar[$otype] = $this->cfg["classes"][$otype]["name"];
				}
				else
				{
					$tar = array(0 => " " . LC_OBJECTS_ALL);
					reset($this->cfg["classes"]);
					while (list($v,) = each($this->cfg["classes"]))
					{
						$name = $this->cfg["classes"][$v]["name"];
						if ($name)
						{
							$tar[$v] = $name;
						};
					}
				};

				// sort type list by name
				asort($tar);
				$retval = $tar;
				break;

			case "parent":
				$retval = $this->get_list(false,true);
				break;

			case "createdby":
				$this->u = get_instance("users");
				$uids[""] = "";
				$this->uids = array_merge($uids,$this->u->listall_acl());
				asort($this->uids);
				$retval = $this->uids;
			
			case "modifiedby":
				$retval = $this->uids;
				

			default:
				
		};
		return $retval;
	}

	function orb_db_query($arr)
	{
		extract($arr);
		$ret = array();
		$this->db_query($sql);
		while ($row = $this->db_next())
		{
			$ret[] = $row;
		}
		return $ret;
	}

	function orb_delete_object($arr)
	{
		extract($arr);
		return $this->delete_object($oid);
	}

	function orb_delete_aliases_of($arr)
	{
		extract($arr);
		return $this->delete_aliases_of($oid);
	}

	function on_site_init($dbi, $site, &$ini_opts)
	{
		// create a few objects to init the db struct
		$mned = get_instance("menuedit");
		$mned->dc = $dbi->dc;	// fake the db connection
		
		if (!$site['site_obj']['use_existing_database'])
		{
			$root_id = $mned->add_new_menu(array(
				"name" => "root",
				"parent" => 0,
				"type" => MN_CLIENT,
				"status" => 2,
				"skip_invalidate" => true
			));

			$client_id = $mned->add_new_menu(array(
				"name" => "klient",
				"parent" => $root_id,
				"type" => MN_CLIENT,
				"status" => 2,
				"skip_invalidate" => true
			));
		}
		else
		{
			$client_id = $site['site_obj']['select_parent_folder'];
			//echo "got client id as $client_id <br>\n";
			flush();
		}

		$site_folder_id = $mned->add_new_menu(array(
			"name" => $site["url"],
			"parent" => $client_id,
			"type" => MN_CLIENT,
			"status" => 2,
			"skip_invalidate" => true
		));

		$ini_opts["rootmenu"] = $site_folder_id;
		$ini_opts["admin_rootmenu2"] = $site_folder_id;
		$ini_opts["per_oid"] = $site_folder_id;

		$awmenu_id = $mned->add_new_menu(array(
			"name" => "Automatweb",
			"parent" => $site_folder_id,
			"type" => MN_CLIENT,
			"status" => 2,
			"skip_invalidate" => true
		));
		$ini_opts["amenustart"] = $awmenu_id;

		// here we gots to export the program menus from the master site. 

		// get list of alla program menus to export
		$mnex = $this->get_objects_below(array(
			'parent' => aw_ini_get("amenustart"),
			'class' => CL_PSEUDO,
			'full' => true,
			'ignore_lang' => true,
			'ret' => ARR_NAME
		));

		// temporarily put back real site id so we can fetch all the correct menus
		$osid = aw_ini_get("site_id");
		$GLOBALS["cfg"]["__default"]["site_id"] = aw_global_get("real_site_id");

		$m_db = get_instance("menu");
		$menus = $m_db->export_menus(array(
			"id" => aw_ini_get("amenustart"),
			"ex_menus" => array_keys($mnex),
			"ret_data" => true,
			"ex_icons" => 1
		));

		// reset to new site id
		$GLOBALS["cfg"]["__default"]["site_id"] = $osid;

		// and now import them to the new site
		$i_p = $menus[0];

		$am = get_instance("admin/admin_menus");
		$am->dc = $dbi->dc;	// fake the db connection

		$am->req_import_menus($i_p, &$menus, $awmenu_id);
		//echo "imported .. <br>\n";
		flush();

		// create another menu with the site name and make menus under that
		$s_rmn_id = $mned->add_new_menu(array(
			"name" => $site['url'],
			"parent" => $site_folder_id,
			"type" => MN_CLIENT,
			"status" => 2,
			"skip_invalidate" => true
		));

		if ($site["select_layout"])
		{
			$mned->set_object_metadata(array(
				"oid" => $s_rmn_id,
				"key" => "show_layout",
				"value" => $site['site_obj']["select_layout"]
			));
		}

		$ini_opts["frontpage"] = $s_rmn_id;

		
		$upmenu_id = $mned->add_new_menu(array(
			"name" => "Ylemine menyy",
			"parent" => $s_rmn_id,
			"type" => MN_CLIENT,
			"status" => 2,
			"skip_invalidate" => true
		));
		$ini_opts["menuedit.menu_defs[$upmenu_id]"] = "YLEMINE";

		// create a couple submenus as well, just as an example
		$mned->add_new_menu(array(
			"name" => "Ylemine 1",
			"parent" => $upmenu_id,
			"type" => MN_CONTENT,
			"status" => 2,
			"skip_invalidate" => true
		));
		$mned->add_new_menu(array(
			"name" => "Ylemine 2",
			"parent" => $upmenu_id,
			"type" => MN_CONTENT,
			"status" => 2,
			"skip_invalidate" => true
		));

		$leftmenu_id = $mned->add_new_menu(array(
			"name" => "Vasak menyy",
			"parent" => $s_rmn_id,
			"type" => MN_CLIENT,
			"status" => 2,
			"skip_invalidate" => true
		));
		$ini_opts["menuedit.menu_defs[$leftmenu_id]"] = "VASAK";

		$mned->add_new_menu(array(
			"name" => "Vasak 1",
			"parent" => $leftmenu_id,
			"type" => MN_CONTENT,
			"status" => 2,
			"skip_invalidate" => true
		));
		$mned->add_new_menu(array(
			"name" => "Vasak 2",
			"parent" => $leftmenu_id,
			"type" => MN_CONTENT,
			"status" => 2,
			"skip_invalidate" => true
		));

		$lo_p_id = $mned->add_new_menu(array(
			"name" => "Login menyy",
			"parent" => $s_rmn_id,
			"type" => MN_CLIENT,
			"status" => 2,
			"skip_invalidate" => true
		));

		$lo_id = $mned->add_new_menu(array(
			"name" => "Sisse loginud",
			"parent" => $lo_p_id,
			"type" => MN_CLIENT,
			"status" => 2,
			"skip_invalidate" => true
		));
		$ini_opts["menuedit.menu_defs[$lo_id]"] = "LOGIN";

		$_tmp = $mned->add_new_menu(array(
			"name" => "Tee t88d",
			"parent" => $lo_id,
			"type" => MN_CONTENT,
			"status" => 2,
			"link" => "/automatweb/"
		));
		$_tmp = $mned->add_new_menu(array(
			"name" => "Lisa dokument",
			"parent" => $lo_id,
			"type" => MN_PMETHOD,
			"status" => 2,
			"pclass" => "document/new",
			"pm_url_admin" => 1
		));
		$_tmp = $mned->add_new_menu(array(
			"name" => "Muuda dokumenti",
			"parent" => $lo_id,
			"type" => MN_PMETHOD,
			"status" => 2,
			"pclass" => "document/change",
			"pm_url_admin" => 1
		));
		$_tmp = $mned->add_new_menu(array(
			"name" => "Logi valja",
			"parent" => $lo_id,
			"type" => MN_CONTENT,
			"status" => 2,
			"link" => "/orb.aw?class=users&action=logout"
		));

		// since no access is given (can't figure out how to do that :( ) we should grant all privileges to everyone by default
		$dbi->db_query("INSERT INTO acl(gid,oid,acl) VALUES(1,$client_id, 9223372036854775807)");
	}

	function orb_get_db_pwd($arr)
	{
		extract($arr);
		return array(
			'base' => aw_ini_get("db.base"),
			'host' => aw_ini_get("db.host"),
			'user' => aw_ini_get("db.user"),
			'pass' => aw_ini_get("db.pass")
		);
	}

	function aw_ini_get_mult($arr)
	{
		extract($arr);
		$ret = array();
		foreach($vals as $vn)
		{
			$ret[$vn] = aw_ini_get($vn);
		}
		return $ret;
	}
}

?>
