<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/admin_menus.aw,v 1.21 2003/07/18 11:54:50 kristo Exp $
class admin_menus extends aw_template
{
	// this will be set to document id if only one document is shown, a document which can be edited
	var $active_doc = false;

	function admin_menus()
	{
		$this->init("automatweb/menuedit");

		// FIXME: damn this is a mess
		$this->lc_load("menuedit","lc_menuedit");
		lc_site_load("menuedit",$this);
		lc_load("definition");
	}

	////
	// !Listib koik objektid
	function db_listall($where = " objects.status != 0",$ignore = false,$ignore_lang = false)
	{
		$aa = "";
		if (!$ignore)
		{
			// loeme sisse koik objektid
			$aa = "AND ((objects.site_id = '".aw_ini_get("site_id")."') OR (objects.site_id IS NULL))";
		};
		if ($this->cfg["lang_menus"] == 1 && $ignore_lang == false)
		{
			$aa.="AND (objects.lang_id=".aw_global_get("lang_id")." OR menu.type = ".MN_CLIENT.")";
		}
		$q = "SELECT objects.oid as oid, 
				objects.parent as parent,
				objects.comment as comment,
				objects.name as name,
				objects.created as created,
				objects.createdby as createdby,
				objects.modified as modified,
				objects.modifiedby as modifiedby,
				objects.last as last,
				objects.status as status,
				objects.jrk as jrk,
				objects.alias as alias,
				objects.class_id as class_id,
				objects.brother_of as brother_of,
				objects.metadata as metadata,
				objects.periodic as periodic,
				menu.type as mtype,
				menu.link as link,
				menu.clickable as clickable,
				menu.target as target,
				menu.ndocs as ndocs,
				menu.img_id as img_id,
				menu.img_url as img_url,
				menu.hide_noact as hide_noact,
				menu.mid as mid,
				menu.sss as sss,
				menu.links as links,
				menu.icon_id as icon_id,
				menu.admin_feature as admin_feature,
				menu.periodic as mperiodic
			FROM objects 
				LEFT JOIN menu ON menu.id = objects.oid
				WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.")  AND $where $aa
				ORDER BY objects.parent, jrk,objects.created";
		$this->db_query($q);
	}

	function get_add_menu($arr)
	{
		extract($arr);

		$this->add_menu = array();
		// check if any parent menus have config objects attached
		$atc_id = 0;
		$ch = $this->get_object_chain($id);
		foreach($ch as $oid => $od)
		{
			if (isset($od["meta"]["add_tree_conf"]))
			{
				$atc_id = $od["meta"]["add_tree_conf"];
			}
		}

		if ($atc_id)
		{
			$atc_inst = get_instance("add_tree_conf");
			$atc_root = $atc_inst->get_root_for_user($atc_id);
			if ($atc_root)
			{
				$mn = $this->get_objects_below(array(
					'parent' => $atc_root,
					'class' => CL_PSEUDO,
					'full' => true,
					'ignore_lang' => true,
					'ret' => ARR_NAME
				)) + array($atc_root => $atc_root);

				$objs = array();
				$mns = join(",",array_keys($mn));
				if ($mns != "")
				{
					$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_OBJECT_TYPE." AND status = 2 AND parent IN (".$mns.") ORDER BY jrk");
					while ($row = $this->db_next())
					{
						$objs[$row["parent"]][] = $row;
					}
				}

				$cnt = 0;
				$counts = array($atc_root => 0);
				foreach($objs as $prnt => $arr)
				{
					$cnt++;
					if (!isset($counts[$prnt]))
					{
						$counts[$prnt] = $cnt;
						$this->add_menu[((int)$counts[$arr[$prnt]["parent"]])][$cnt] = array(
							"caption" => $mn[$prnt],
							"link" => ""
						);
					}
					if (is_array($arr))
					{
						foreach($arr as $row)
						{
							$meta = $this->get_object_metadata(array("metadata" => $row["metadata"]));

							if ($meta["type"] == "__all_objs")
							{
								$cldata = array();
								foreach($this->cfg["classes"] as $clid => $_cldata)
								{
									if ($_cldata["name"] != "")
									{
										$cldata[$_cldata["name"][0]][$_cldata["file"]] = $_cldata["name"];
									}
								}

								ksort($cldata);
								foreach($cldata as $letter => $clns)
								{
									$cnt++;
									$this->add_menu[((int)$counts[$prnt])][$cnt] = array(
										"caption" => $letter,
										"link" => ""
									);
									$cp = $cnt;
									asort($clns);
									foreach($clns as $cl_file => $cl_name)
									{
										$addlink = $this->mk_my_orb("new", array("parent" => $id, "period" => $period), $cl_file, true, true);

										$cnt++;
										$this->add_menu[((int)$cp)][$cnt] = array(
											"caption" => $cl_name,
											"link" => $addlink
										);
									}
								}
							}
							else
							{
								$addlink = $this->mk_my_orb("new", array("parent" => $id, "period" => $period), $this->cfg["classes"][$meta["type"]]["file"], true, true);
								$cnt++;
								$this->add_menu[((int)$counts[$row["parent"]])][$cnt] = array(
									"caption" => $row["name"],
									"link" => $addlink
								);
							}
						}
					}
				}
				return;
			}
		}
		return ($this->req_get_default_add_menu(0, $id, $period, 0));
	}

	function get_az_def_menu($pt, $parent, $period, $fld_id)
	{
		$cldata = array();
		foreach($this->cfg["classes"] as $clid => $_cldata)
		{
			if (!empty($_cldata["name"]) && $_cldata["can_add"])
			{
				$cldata[$_cldata["name"][0]][$_cldata["file"]] = $_cldata["name"];
			}
		}

		ksort($cldata);
		foreach($cldata as $letter => $clns)
		{
			$this->add_menu[$fld_id]["letter_" . $letter] = array(
				"caption" => $letter,
				"list" => "list",
			);
			asort($clns);
			foreach($clns as $cl_file => $cl_name)
			{
				$addlink = $this->mk_my_orb("new", array("parent" => $parent, "period" => $period), $cl_file, true, true);
				$this->add_menu["letter_" . $letter]["letter_" . $letter . $cl_file] = array(
					"caption" => $cl_name,
					"link" => $addlink,
					"list" => "list",
				);
			}
		}
	}

	function req_get_default_add_menu($prnt, $parent, $period, $fld_id = 0)
	{
		$ret = "";
		# see teeb esimese taseme klassid
		if (is_array($this->cfg["classes"]) && $prnt == 0)
		{
			$tcnt = 0;
			foreach($this->cfg["classes"] as $clid => $cldata)
			{
				if (isset($cldata["parents"]))
				{
					$parens = explode(",", $cldata["parents"]);
					if (in_array($prnt, $parens))
					{
						$addlink = $this->mk_my_orb("new", array("parent" => $parent, "period" => $period), $cldata["file"], true, true);
						$tcnt++;
						$this->add_menu[0][$tcnt] = array(
							"caption" => $cldata["name"],
							"link" => $addlink,
						);
					}
				}
			}
			$tcnt++;
			/*
			$this->add_menu[0][$tcnt] = array(
				"separator" => true,
			);
			*/
		}

		# see hoolitseb s
		if (is_array($this->cfg["classes"]) && $prnt != 0)
		{
			foreach($this->cfg["classes"] as $clid => $cldata)
			{
				if (!empty($cldata["parents"]) && $cldata["can_add"])
				{
					$parens = explode(",", $cldata["parents"]);
					if (in_array($prnt, $parens))
					{
						$addlink = $this->mk_my_orb("new", array("parent" => $parent, "period" => $period), $cldata["file"], true, true);
						$this->add_menu[$fld_id]["class_" . $clid] = array(
							"caption" => $cldata["name"],
							"link" => $addlink,
						);
					}
				}
			}
		}

		if (is_array($this->cfg["classfolders"]))
		{
			// uh, but isn't this highly inefficient? to cycle over the array each 
			// fscking time?
			foreach($this->cfg["classfolders"] as $fid => $fdata)
			{
				if ($fdata["parent"] == $prnt)
				{
					$_fid = "fld_" . $fid;
					$_fprnt = ($fdata["parent"] == 0) ? 0 : "fld_" . $prnt;
					$this->add_menu[$_fprnt][$_fid] = array(
						"caption" => $fdata["name"],
						"link" => "",
					);
					$this->req_get_default_add_menu($fid, $parent, $period, $_fid);
					if (isset($fdata["all_objs"]))
					{
						$this->get_az_def_menu($fid, $parent, $period, $_fid);
					}
					if (isset($fdata["docforms"]))
					{
						$d = get_instance("doc");
						$this->add_menu[$_fid] = $d->get_doc_add_menu($parent,$period);
					};
	
					if (isset($fdata["separator"]))
					{	
						$this->add_menu[$_fprnt][] = array(
							"separator" => true,
						);
					};

				}
			}
		}

		return $ret;
	}

	function get_popup_data($args = array())
	{
		extract($args);
		if (isset($addmenu) && $addmenu == 1)
		{
			$this->get_add_menu($args);
		}

		if (!is_array($obj))
		{
			$obj = $this->get_object($id);
		}

		$sep = "\n";
		if ($sharp)
		{
			$sep = "#";
		}

		$baseurl = $this->cfg["baseurl"];
		$retval = "";

		if ($obj["class_id"] == CL_PSEUDO)
		{
			$ourl = $this->mk_my_orb("right_frame", array("id" => $id, "parent" => $obj["oid"],"period" => $period), "admin_menus",true,true);
			$this->vars(array(
				"link" => $ourl,
				"text" => "Open"
			));
			$retval = $this->parse("MENU_ITEM");
		}

		if ($this->can("edit", $id))
		{
			$churl = $this->mk_my_orb("change", array("id" => $id, "parent" => $obj["parent"],"period" => $period), $this->cfg["classes"][$obj["class_id"]]["file"],true,true);

			$this->vars(array(
				"link" => $churl,
				"text" => "Change"
			));
			$retval .= $this->parse("MENU_ITEM");

			$cuturl = $this->mk_my_orb("cut", array("reforb" => 1, "id" => $id, "parent" => $obj["parent"],"sel[$id]" => "1"), "admin_menus",true,true);

			$this->vars(array(
				"link" => $cuturl,
				"text" => "Cut"
			));
			$retval .= $this->parse("MENU_ITEM");
		}

		$copyurl = $this->mk_my_orb("copy", array("reforb" => 1, "id" => $id, "parent" => $obj["parent"],"sel[$id]" => "1","period" => $period), "admin_menus",true,true);

		$this->vars(array(
			"link" => $copyurl,
			"text" => "Copy"
		));
		$retval .= $this->parse("MENU_ITEM");

		if ($this->can("delete", $id))
		{
			$delurl = $this->mk_my_orb("delete", array("reforb" => 1, "id" => $id, "parent" => $obj["parent"],"sel[$id]" => "1","period" => $period), "admin_menus",true,true);
		
			$this->vars(array(
				"link" => $delurl,
				"text" => "Delete"
			));
			$retval .= $this->parse("MENU_ITEM");
		}

		if ($this->can("admin", $id))
		{
			$delurl = "javascript:go_acl(".$id.")";
			$this->vars(array(
				"link" => $delurl,
				"text" => "ACL"
			));
			$retval .= $this->parse("MENU_ITEM");
		}

		if ($ret_data)
		{
			return $retval;
		}

		print $retval;
		exit;
	}

	function get_feature_icon_url($fid)
	{
		return aw_ini_get("icons.server")."/prog_".$fid.".gif";
	}

	////
	// !shows menus importing form
	function import($arr)
	{
		extract($arr);
		$this->mk_path($parent,LC_MENUEDIT_IMPORT_MENU);
		$this->read_template("import.tpl");
		$this->vars(array("reforb" => $this->mk_reforb("submit_import", array("parent" => $parent))));
		return $this->parse();
	}

	////
	// !does the actual menu importing bit
	function submit_import($arr)
	{
		extract($arr);

		$updmenus = array();
		if ($file_type == "text")
		{
			$this->do_text_import($arr);
		}
		else
		{
			global $fail;

			$f = @fopen($fail, "r");
			if ($f)
			{
				$d = fread($f,filesize($fail));
				fclose($f);
			}
			else
			{
				return $this->mk_my_orb("import",array("parent" => $parent));
			};

			$menus = unserialize($d);
			$i_p = $menus[0];

			$this->req_import_menus($i_p, &$menus, $parent);
		}

		$this->invalidate_menu_cache();

		return $this->mk_my_orb("right_frame", array("parent" => $parent));
//		return $url = "javascript:go_go(".$parent.",'')";
	}

	function req_import_menus($i_p, &$menus, $parent)
	{
		if (!is_array($menus[$i_p]))
		{
			return;
		}
		$mt = $this->db_fetch_field("SELECT type FROM menu WHERE id= $parent","type");
		$i = get_instance("icons");
		reset($menus[$i_p]);
		while (list(,$v) = each($menus[$i_p]))
		{
			$db = $v["db"];
	
			$icon_id = 0;
			if (is_array($v["icon"]))
			{
				$icon_id = $i->get_icon_by_file($v["icon"]["file"]);
				if (!$icon_id)
				{
					// not in db, must add
					$icon_id = $i->add_array($v["icon"]);
				}
			}
			if ($mt == MN_HOME_FOLDER || $mt == MN_HOME_FOLDER_SUB)
			{
				$db["mtype"] = MN_HOME_FOLDER_SUB;	// so you can share them later on.
			}

			$id = $this->new_object(array("parent" => $parent,"name" => $db["name"], "class_id" => $db["class_id"], "status" => $db["status"], "comment" => $db["comment"], "jrk" => $db["jrk"], "visible" => $db["visible"], "alias" => $db["alias"], "periodic" => $db["periodic"]));
			$this->db_query("INSERT INTO menu 
						 (id,link,type,is_l3,periodic,clickable,target,mid,hide_noact,ndocs,admin_feature,number,icon_id,links) 
			VALUES ($id,'".$db["link"]."','".$db["mtype"]."','".$db["is_l3"]."','".$db["periodic"]."','".$db["clickable"]."','".$db["target"]."','".$db["mid"]."','".$db["hide_noact"]."','".$db["ndocs"]."','".$db["admin_feature"]."','".$db["number"]."',$icon_id,'".$db["links"]."')");

			// tegime vanema menyy 2ra, teeme lapsed ka.
			$this->req_import_menus($db["oid"],$menus,$id);
		}
	}

	////
	// !cuts the selected objects
	function cut($arr)
	{
		extract($arr);

		$cut_objects = array();
		if (is_array($sel))
		{
			foreach($sel as $oid => $one)
			{
				$cut_objects[$oid] = $oid;
			}
		}

		aw_session_set("cut_objects",$cut_objects);

		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
	}

	////
	// !copies the selected objects
	function copy($arr)
	{
		extract($arr);

		// check if any objects that are to be copied need special handling
		if (is_array($sel))
		{
			foreach($sel as $oid => $one)
			{
				$ob = $this->get_object($oid);
				if ($ob["class_id"] == CL_PSEUDO)
				{
					return $this->mk_my_orb("copy_feedback", array("parent" => $parent, "period" => $period, "sel" => $sel));
				}
			}
		}

		// if not, just copy the damn things
		$copied_objects = array();
		if (is_array($sel))
		{
			foreach($sel as $oid => $one)
			{
				$r = $this->serialize(array("oid" => $oid));
				if ($r != false)
				{
					$copied_objects[$oid] = $r;
				}
			}
		}
		aw_session_set("copied_objects", $copied_objects);

		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
	}

	function copy_feedback($arr)
	{
		extract($arr);
		$this->read_template("copy_feedback.tpl");
		$this->mk_path($parent, "Vali kuidaws objekte kopeerida");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_copy_feedback", array("parent" => $parent, "period" => $period,"sel" => $sel))
		));

		return $this->parse();
	}

	function submit_copy_feedback($arr)
	{
		extract($arr);
		aw_register_default_class_member("admin_menus", "serialize_submenus", $ser_submenus);
		aw_register_default_class_member("admin_menus", "serialize_subobjs",$ser_subobjs);

		$copied_objects = array();
		if (is_array($sel))
		{
			foreach($sel as $oid => $one)
			{
				$r = $this->serialize(array("oid" => $oid));
				if ($r != false)
				{
					$copied_objects[$oid] = $r;
				}
			}
		}
		aw_session_set("copied_objects", $copied_objects);
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
	}

	////
	// !pastes the cut objects 
	function paste($arr)
	{
		extract($arr);

		$cut_objects = aw_global_get("cut_objects");
		$copied_objects = aw_global_get("copied_objects");

		if (is_array($cut_objects))
		{
			reset($cut_objects);
			while (list(,$oid) = each($cut_objects))
			{
				if ($oid != $parent)
				{
					// so, let the object update itself when it is being cut-pasted, if it so desires
					$obj = $this->get_object($oid);
					if ($this->cfg["classes"][$obj["class_id"]]["file"] != "")
					{
						$inst = get_instance($this->cfg["classes"][$obj["class_id"]]["alias_class"] != "" ? $this->cfg["classes"][$obj["class_id"]]["alias_class"] : $this->cfg["classes"][$obj["class_id"]]["file"]);
						if (method_exists($inst, "cut_hook"))
						{
							$inst->cut_hook(array(
								"oid" => $oid,
								"new_parent" => $parent
							));
						}
					}
					
					$this->upd_object(array(
						"oid" => $oid, 
						"parent" => $parent,
						"period" => $period,
						"lang_id" => aw_global_get("lang_id")
					));
				}
			}
		}
		aw_session_set("cut_objects",array());

		if (is_array($copied_objects))
		{
			reset($copied_objects);
			while (list($oid,$str) = each($copied_objects))
			{
				$this->unserialize(array("str" => $str, "parent" => $parent, "period" => $period));
			}
		}

		$this->invalidate_menu_cache();

		$GLOBALS["copied_objects"] = array();
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
		//return "javascript:go_go(".$parent.",'".$period."')";
	}

	function o_delete($arr)
	{
		extract($arr);
		if (is_array($sel))
		{
			reset($sel);
			while (list($ooid,) = each($sel))
			{
				$this->delete_object($ooid);
				$this->delete_aliases_of($ooid);
			}
		}
		if ($oid)
		{
			$this->delete_object($oid);
		}
		return $this->mk_orb("obj_list", array("parent" => $parent, "period" => $period));
		aw_session_set("copied_objects",array());
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
		//return "javascript:go_go(".$parent.",'".$period."')";
	}

	function make_menu_caches($where = "objects.status = 2")
	{
		$mc = get_instance("menu_cache");
		$mc->make_caches();
		upd_instance("menu_cache",$mc);
		$this->subs =  $mc->get_ref("subs");
		$this->mar =  $mc->get_ref("mar");
		$this->mpr =  $mc->get_ref("mpr");
	}

	function invalidate_menu_cache()
	{
		$cache = get_instance("cache");

		// here we gots to invalidate the objects::get_list cache as well, cause it also contains menus
		$cache->file_invalidate_regex("objects::get_list::.*");
		$cache->file_invalidate_regex("menuedit::menu_cache::.*");
	}

	////	
	// !imports menus from text file. file format description is at http://aw.struktuur.ee/index.aw?section=38624
	function do_text_import($arr)
	{
		global $fail;
		if (is_uploaded_file($fail))
		{
			$c = file($fail);
			$cnt = 0;
			$levels = array("" => $parent); // here we keep the info about the numbering of the levels => menu id's
			foreach($c as $row)
			{
				$cnt++;
				// parse row and create menu.
				if (!preg_match("/([0-9\.]+)(.*)\[(.*)\]/",$row,$mt))
				{
					if (!preg_match("/([0-9\.]+)(.*)/",$row,$mt))
					{
						$this->raise_error(ERR_MNEDIT_TXTIMP,"Menyyde importimisel tekkis viga real $cnt ",true);
					}
				}
				// now parse the position in the structure from the numbers.
				$pos = strrpos($mt[1],".");
				$_pt = substr($mt[1],0,$pos);
				if ($_pt == "")
				{
					$_parent = $arr["parent"];
				}
				else
				{
					$_parent = $levels[$_pt];
				}

				if ($_pt != "" && !$_parent)
				{
					$this->raise_error(ERR_MNEDIT_TXTIMP_PARENT,"Menyyde importimisel ei leidnud parent menyyd real $cnt ",true);
				}
				else
				{
					// parse the menu options
					$opts = trim($mt[3]);
					$mopts = array("click" => 1);
					if ($opts != "")
					{
						// whee. do a preg_match for every option. 
						$mopts["act"] = preg_match("/\+act/",$opts);
						if (preg_match("/\+comment=\"(.*)\"/",$opts,$mmt))
						{
							$mopts["comment"] = $mmt[1];
						}
						if (preg_match("/\+alias=(.*)/",$opts,$mmt))
						{
							$mopts["alias"] = $mmt[1];
						}
						$mopts["per"] = preg_match("/\+per/",$opts);
						if (preg_match("/\+link=\"(.*)\"/",$opts,$mmt))
						{
							$mopts["link"] = $mmt[1];
						}
						$mopts["click"] = preg_match("/\+click/",$opts);
						$mopts["target"] = preg_match("/\+target/",$opts);
						$mopts["mid"] = preg_match("/\+mid/",$opts);
						$mopts["makdp"] = preg_match("/\+makdp/",$opts);
						if (preg_match("/\+width=\"(.*)\"/",$opts,$mmt))
						{
							$mopts["width"] = $mmt[1];
						}
						$mopts["rp"] = preg_match("/\+rp/",$opts);
						$mopts["lp"] = preg_match("/\+lp/",$opts);
						if (preg_match("/\+fn=\"(.*)\"/",$opts,$mmt))
						{
							$mopts["fn"] = $mmt[1];
						}
					}

					// now create the damn thing.
					$this->quote(&$mt);
					$this->quote(&$mopts);
					$id = $this->new_object(array(
						"parent" => $_parent,
						"class_id" => CL_PSEUDO,
						"name" => trim($mt[2]),
						"comment" => $mopts["comment"],
						"status" => ($mopts["act"] ? 2 : 1),
						"alias" => $mopts["alias"],
						"jrk" => substr($mt[1],($pos > 0 ? $pos+1 : 0))
					));

					if ($mopts["fn"] != "")
					{
						$this->set_object_metadata(array(
							"oid" => $id,
							"key" => "aip_filename",
							"value" => $mopts["fn"]
						));
					}
					$this->db_query("INSERT INTO menu (id,type,link,clickable,target,mid,hide_noact,width,right_pane,left_pane)
						VALUES($id,".MN_CONTENT.",'".$mopts["link"]."','".$mopts["click"]."','".$mopts["target"]."','".$mopts["mid"]."','".$mopts["makdp"]."','".$mopts["width"]."','".(!$mopts["rp"])."','".(!$mopts["lp"])."')");
					$levels[$mt[1]] = $id;
				}
			}
		}
	}

	function setup_rf_table($parent)
	{
		load_vcl("table");
		$this->t = new aw_table(array("prefix" => "me_rf"));

		$this->co_id = 0;

		// check if any parent menus have config objects attached 
		$ch = $this->get_object_chain($parent);
		foreach($ch as $oid => $od)
		{
			if (isset($od["meta"]["objtbl_conf"]))
			{
				$this->co_id = $od["meta"]["objtbl_conf"];
			}
		}

		if (!$this->co_id)
		{
			$this->t->parse_xml_def($this->cfg["basedir"]."/xml/menuedit/right_frame_default.xml");
		}
		else
		{
			$this->t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
			$this->otc_inst = get_instance("obj_table_conf");
			$this->otc_inst->init_table($this->co_id, $this->t);
		}
	}

	////
	// !Displays the right frame table .. uh, what a name. 
	function right_frame($arr)
	{
		extract($arr);
		if (!$this->prog_acl("view", PRG_MENUEDIT))
		{
			$this->acl_error("view", PRG_MENUEDIT);
		}

		get_instance("icons");

		$lang_id = aw_global_get("lang_id");
		$site_id = $this->cfg["site_id"];
		$parent = !empty($parent) ? $parent : $this->cfg["rootmenu"];
		$sel_objs = aw_global_get("cut_objects");
		if (!is_array($sel_objs))
		{
			$sel_objs = array();
		}
		$t = aw_global_get("copied_objects");
		if (!is_array($t))
		{
			$t = array();
		}
		$sel_objs+=$t;

		$this->mk_path($parent,"",$period);

		$la = get_instance("languages");
		$lar = $la->get_list();

		$this->setup_rf_table($parent);

		$host = str_replace("http://","",$this->cfg["baseurl"]);
		if (preg_match("/.*:(.+?)/U",$host, $mt))
		{
			if (isset($mt[1]))
			{
				$host = str_replace(":".$mt[1], "", $host);
			}
		};

		$ps = "";

		if ($period)
		{
			$ps = " AND ((objects.period = '$period') OR (objects.class_id = ".CL_PSEUDO." AND objects.periodic = 1)) ";
		}

		$this->read_template("js_popup_menu.tpl");

		// do not show relation objects in the list. hm, I wonder whether
		// I'll burn in hell for this --duke
		$cls = " AND objects.class_id != " . CL_RELATION;

		// would be nice if we would only query the fields we actually need, otherwise
		// we just spend a lot of memory on nothing when handling long object lists.
		// BUT doing this right now would break the custom object list thingie ... -- duke

		// by the way, mk_my_orb is pretty expensive and all those calls to it
		// here take up to 10% of the time used to create the page -- duke

		$per_page = 100;
		$ft_page = $GLOBALS["ft_page"];
		$lim = "LIMIT ".($ft_page * $per_page).",".$per_page;

		$where = "objects.parent = '$parent' AND 
				(lang_id = '$lang_id' OR m.type = ".MN_CLIENT." OR objects.class_id IN(".CL_PERIOD .",".CL_USER.",".CL_GROUP.",".CL_MSGBOARD_TOPIC."))
				 AND 
				status != 0 
				$cls $ps ";

		$query = "FROM objects 
				LEFT JOIN menu m ON m.id = objects.oid
			WHERE 
				$where";

		// make pageselector.
		$_t = new aw_table;

		// total count
		$q = "SELECT count(*) as cnt $query";
		$_t->d_row_cnt = $this->db_fetch_field($q, "cnt");

		if ($_t->d_row_cnt > $per_page)
		{
			$pageselector = $_t->draw_lb_pageselector(array(
				"records_per_page" => $per_page
			));
		}

		$q = "SELECT objects.* $query $lim";
		$this->db_query($q);

		// perhaps this should even be in the config file?
		$containers = array(CL_PSEUDO,CL_BROTHER,CL_PROMO,CL_GROUP,CL_MSGBOARD_TOPIC);

		$num_records = 0;

		while ($row = $this->db_next())
		{
			if (!$this->can("view", $row["oid"]))
			{
				continue;
			}
			$can_change = $this->can("edit", $row["oid"]);
			$can_delete = $this->can("delete", $row["oid"]);
			$can_admin = $this->can("admin", $row["oid"]);

			$row["is_menu"] = 0;
			if (in_array($row["class_id"],$containers))
			{
				$chlink = $this->mk_my_orb("right_frame", array("parent" => $row["oid"], "period" => $period));
				$row["is_menu"] = 1;
			}
			else
			if ($row["class_id"] == CL_PLANNER)
			{
				$chlink = $this->mk_my_orb("change",array("id" => $row["oid"]),"planner");
			}
			else
			{
				$chlink = $this->mk_my_orb("view", array("id" => $row["oid"], "period" => $period),$this->cfg["classes"][$row["class_id"]]["file"]);
			}

			$dellink = $this->mk_my_orb("delete", array("reforb" => 1, "id" => $row["oid"], "parent" => $row["parent"],"sel[".$row["oid"]."]" => "1"), "admin_menus",true,true);
			
			if (isset($sel_objs[$row["oid"]]))
			{
				$row["cutcopied"] = "#E2E2DB";
			}
			else
			{
				$row["cutcopied"] = "#FCFCF4";
			}
			$iu = icons::get_icon_url($row["class_id"],$row["name"]);

			$row["lang_id"] = $lar[$row["lang_id"]];

			$this->save_handle();
			$this->vars(array(
				"menu_id" => "js_pop_".$row["oid"],
				"menu_icon" => $this->cfg["baseurl"]."/automatweb/images/blue/obj_settings.gif",
				"MENU_ITEM" => $this->get_popup_data(array(
					"period" => $period,
					"id" => $row["oid"], 
					"ret_data" => true, 
					"sharp" => true,
					"type" => "js",
					"obj" => $row
				))
			));
			$row["java"] = $this->parse();

			$this->restore_handle();

//			$row["icon"] = '<img src="'.$iu.'">';
			$this->t->set_default_sortby(array("name" => "icon+name"));
			$caption = ($row["name"] == '' ? "(nimeta)" : $row["name"]);

			$row["name"] = '<!-- '.$caption.' --><a href="'.$chlink.'"><img src="'.$iu.'" border="0">&nbsp;&nbsp;&nbsp;'.$caption."</a>";

			$row["link"] = "<a href=\"".$this->cfg["baseurl"]."/".$row["oid"]."\">Link</a>";
			$row["class_id"] = $this->cfg["classes"][$row["class_id"]]["name"];
			$row["hidden_jrk"] = $row["jrk"];
			if ($can_change)
			{
				$row["jrk"] = "<input type=\"hidden\" name=\"old[jrk][".$row["oid"]."]\" value=\"".$row["jrk"]."\"><input type=\"text\" name=\"new[jrk][".$row["oid"]."]\" value=\"".$row["jrk"]."\" class=\"formtext\" size=\"3\">";
			}

			if ($can_change)
			{
				$row["status"] = "<input type=\"hidden\" name=\"old[status][".$row["oid"]."]\" value=\"".$row["status"]."\"><input type=\"checkbox\" name=\"new[status][".$row["oid"]."]\" value=\"2\" ".checked($row["status"] == 2).">";
			}
			else
			{
				$row["status"] = $row["status"] == 1 ? "Mitteaktiivne" : "Aktiivne";
			}

			if ($can_change)
			{
				$row["select"] = "<input type=\"checkbox\" name=\"sel[".$row["oid"]."]\" value=\"1\">";
			}
			else
			{
				$row["select"] = "&nbsp;";
			}

			$row["change"] = $can_change ? "<a href=\"$chlink\"><img src=\"".$this->cfg["baseurl"]."/automatweb/images/blue/obj_settings.gif\" border=\"0\"></a>" : "";
			$row["acl"] = $can_admin ? "<a href=\"editacl.aw?oid=".$row["oid"]."&file=default.xml\"><img src=\"".$this->cfg["baseurl"]."/automatweb/images/blue/obj_acl.gif\" border=\"0\"></a>" : "";
			if ($this->co_id)
			{
				$this->otc_inst->table_row($row, &$this->t);
			}
			else
			{
				$this->t->define_data($row);
			}

			$num_records++;

		}
		$this->get_add_menu(array(
			"id" => $parent,
			"ret_data" => true,
			"sharp" => true,
			"addmenu" => 1,
			"period" => $period,
		));

		$this->read_template("js_add_menu.tpl");

		$whole_menu = "";

		foreach($this->add_menu as $item_id => $item_collection)
		{
			$menu_data = "";
			foreach($item_collection as $el_id => $el_data)
			{
				// if this el_id has children, make it a submenu
				$children = isset($this->add_menu[$el_id]) ? sizeof($this->add_menu[$el_id]) : 0;
				if (isset($el_data["separator"]))
				{
					$tpl = "MENU_SEPARATOR";
				}
				elseif ($children > 0)
				{
					$tpl = "MENU_ITEM_SUB";
				}
				else
				{
					$tpl = "MENU_ITEM";
				};
			
				$this->vars(array(
					"caption" => isset($el_data["caption"]) ? $el_data["caption"] : "",
					"url" => isset($el_data["link"]) ? $el_data["link"] : "",
					"sub_menu_id" => "aw_menu_" . $el_id,
				));
				$menu_data .= $this->parse($tpl);
			};
			$this->vars(array(
				"MENU_ITEM" => $menu_data,
				"menu_id" => "aw_menu_" . $item_id,
			));
			$whole_menu .= $this->parse("MENU");
		};

		// make applet for adding objects
		$this->vars(array(
			"icon_over" => $this->cfg["baseurl"]."/automatweb/images/icons/new2_over.gif",
			"icon" => $this->cfg["baseurl"]."/automatweb/images/icons/new2.gif",
			"oid" => $parent,
			"bgcolor" => "#D4D7DA",
			"nr" => 2,
			"key" => "addmenu",
			"val" => 1,
			"name" => "",
			"height" => 22,
			"width" => 23,
		));

		$la = get_instance("languages");

		if (!$sortby)
		{
			$sortby = "hidden_jrk";
		};

		if (!$sort_order)
		{
			$sort_order = "asc";
		};

		$this->t->set_numeric_field("hidden_jrk");

		$this->t->sort_by(array(
			"field" => array("is_menu", $sortby),
			"sorder" => array("is_menu" => "desc", $sortby => $sort_order)
		));

		$this->read_template("right_frame.tpl");

		$toolbar = $this->rf_toolbar(array(
			"parent" => $parent,
			"add_applet" => $whole_menu,
			"sel_count" => count($sel_objs),
		));

		$toolbar_data = $toolbar->get_toolbar();
		$toolbar_data .= $whole_menu;

		$this->vars(array(
			"table" => $pageselector.$this->t->draw(),
			"reforb" => $this->mk_reforb("submit_rf", array("parent" => $parent, "period" => $period, "sortby" => $sortby, "sort_order" => $sort_order)),
			"parent" => $parent,
			"period" => $period,
			"lang_name" => $la->get_langid(),
			"toolbar" => $toolbar_data,
		));

		return $this->parse();
	}

	function rf_toolbar($args = array())
	{
		extract($args);
		$toolbar = get_instance("toolbar");
		
		if ($this->can("add", $parent))
		{
			$toolbar->add_button(array(
				"name" => "add",
				"tooltop" => "Uus",
				"url" => "#",
				"onClick" => "return buttonClick(event, 'aw_menu_0');",
				"img" => "new.gif",
				"imgover" => "new_over.gif",
				"class" => "menuButton",
			));
		};

		if (empty($no_save))
		{
			$toolbar->add_button(array(
				"name" => "save",
				"tooltip" => "Salvesta",
				"url" => "javascript:document.foo.submit()",
				"imgover" => "save_over.gif",
				"img" => "save.gif",
			));
		};
		
		$toolbar->add_button(array(
			"name" => "search",
			"tooltip" => "Otsi",
			"url" => $this->mk_my_orb("search",array("parent" => $parent),"search"),
			"imgover" => "search_over.gif",
			"img" => "search.gif",
		));
		

		$toolbar->add_separator();

		$toolbar->add_button(array(
			"name" => "cut",
			"tooltip" => "Cut",
			"url" => "javascript:submit('cut')",
			"imgover" => "cut_over.gif",
			"img" => "cut.gif",
		));

		$toolbar->add_button(array(
			"name" => "copy",
			"tooltip" => "Copy",
			"url" => "javascript:submit('copy')",
			"imgover" => "copy_over.gif",
			"img" => "copy.gif",
		));

		if ($sel_count > 0)
		{
			$toolbar->add_button(array(
				"name" => "paste",
				"tooltip" => "Paste",
				"url" => "javascript:submit('paste')",
				"imgover" => "paste_over.gif",
				"img" => "paste.gif",
			));
		};

		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => "Delete",
			"url" => "javascript:submit('delete')",
			"imgover" => "delete_over.gif",
			"img" => "delete.gif",
		));

		$toolbar->add_button(array(
			"name" => "edit",
			"tooltip" => "Edit",
			"url" => "javascript:change()",
			"imgover" => "edit_over.gif",
			"img" => "edit.gif",
		));
		
		$toolbar->add_separator();
	
		$toolbar->add_button(array(
			"name" => "refresh",
			"tooltip" => "Refresh",
			"url" => "javascript:window.location.reload()",
			"imgover" => "refresh_over.gif",
			"img" => "refresh.gif",
		));
	
		$toolbar->add_button(array(
			"name" => "import",
			"tooltip" => "Import",
			"url" => $this->mk_my_orb("import",array("parent" => $parent)),
			"imgover" => "import_over.gif",
			"img" => "import.gif",
		));
	
		if (isset($callback) && is_array($callback) && sizeof($callback) == 2)
		{
			$callback[0]->$callback[1](array("toolbar" => &$toolbar));
		};

		return $toolbar;
	}

	function submit_rf($arr)
	{
		extract($arr);

		if (is_array($old))
		{
			foreach($old as $column => $coldat)
			{
				foreach($coldat as $oid => $oval)
				{
					$val = $new[$column][$oid];
					if ($column == "status" && $val == 0)
					{
						$val = 1;
					}
					if ($val != $oval)
					{
						$this->upd_object(array(
							"oid" => $oid,
							$column => $val
						));
					}
				}
			}
		}
		$this->invalidate_menu_cache();
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period, "sortby" => $sortby, "sort_order" => $sort_order));
	}

	function new_delete($arr)
	{
		extract($arr);
		if (is_array($sel))
		{
			$oids = join(",",array_keys($sel));
			$this->db_query("SELECT oid,class_id FROM objects WHERE oid IN($oids)");
			while ($row = $this->db_next())
			{
				$this->save_handle();
				if ($this->can("delete", $row["oid"]))
				{
					if ($this->cfg["classes"][$row["class_id"]]["file"] != "")
					{
						$inst = get_instance($this->cfg["classes"][$row["class_id"]]["alias_class"] != "" ? $this->cfg["classes"][$row["class_id"]]["alias_class"] : $this->cfg["classes"][$row["class_id"]]["file"]);
						if (method_exists($inst, "delete_hook"))
						{
							$inst->delete_hook(array("oid" => $row["oid"]));
						}
					}
					$this->delete_object($row["oid"]);
				}
				$this->restore_handle();
			}
		}
		$this->invalidate_menu_cache();
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period, "sortby" => $sortby, "sort_order" => $sort_order));
	}

	function change_redir($arr)
	{
		extract($arr);
		if (!is_array($sel))
		{
			$this->raise_error(ERR_MNED_NO_OBJS,"Valitud pole &uuml;htegi objekti!", true);
		}

		reset($sel);
		list($oid,) = each($sel);

		$obj = $this->get_object($oid);
		//return $this->mk_my_orb("change", array("id" => $oid, "parent" => $parent), $this->cfg["classes"][$obj["class_id"]]["file"]);
		return "javascript:go_change('".basename($this->cfg["classes"][$row["class_id"]]["file"])."',".$oid.",".$parent.")";
	}

	function req_serialize_obj_tree($oid)
	{
		$objs = $this->list_objects(array("class" => CL_PSEUDO, "parent" => $oid, "return" => ARR_ALL));
		$oids = join(",", array_keys($objs));
		if ($oids != "")
		{
			$this->db_query("SELECT * FROM menu WHERE id IN ($oids)");
			while ($row = $this->db_next())
			{
				$cur_id = $row["id"];

				$hash = gen_uniq_id();
				$this->menu_hash2id[$cur_id] = $hash;

				$od = $objs[$cur_id];
				$od["parent"] = $this->menu_hash2id[$od["parent"]];
				$od["oid"] = $hash;
				$row["id"] = $hash;

				$dat = array(
					"object" => $od,
					"table" => $row
				);
				$this->ser_obj[$hash] = $dat;

				$this->save_handle();
				$this->req_serialize_obj_tree($cur_id);
				$this->restore_handle();
			}
		}
		if ($this->serialize_subobjs || aw_global_get("__is_rpc_call"))
		{
			$this->db_query("SELECT oid FROM objects WHERE parent = $oid AND status != 0 AND class_id != ".CL_PSEUDO." AND lang_id = '".aw_global_get("lang_id")."' AND site_id = '".$this->cfg["site_id"]."'");
			while ($row = $this->db_next())
			{
				$dat = $this->serialize(array("oid" => $row["oid"]));
				if ($dat !== false)
				{
					$hash = gen_uniq_id();
					$this->ser_obj[$hash] = array("is_object" => true, "objstr" => $dat, "parent" => $this->menu_hash2id[$oid]);
				}
			}
		}
	}

	////
	// !this should creates a string representation of the menu
	// parameters
	//    oid - menu id
	function _serialize($arr)
	{
		extract($arr);
		$this->ser_obj = array();
		$hash = gen_uniq_id();
		$this->menu_hash2id[$oid] = $hash;
		$od = $this->get_object($oid);
		$od["parent"] = 0;
		$od["oid"] = $hash;

		$row = $this->db_fetch_row("SELECT * FROM menu WHERE id = '$oid'");
		$row["id"] = $hash;
		$dat = array(
			"object" => $od,
			"table" => $row
		);
		$this->ser_obj[$hash] = $dat;

		if ($this->serialize_submenus || aw_global_get("__is_rpc_call"))
		{
			$this->req_serialize_obj_tree($oid);
		}
		else
		{
			if ($this->serialize_subobjs || aw_global_get("__is_rpc_call"))
			{
				$this->db_query("SELECT oid FROM objects WHERE parent = $oid AND status != 0 AND class_id != ".CL_PSEUDO." AND lang_id = '".aw_global_get("lang_id")."' AND site_id = '".$this->cfg["site_id"]."'");
				while ($row = $this->db_next())
				{
					$dat = $this->serialize(array("oid" => $row["oid"]));
					if ($dat !== false)
					{
						$hash = gen_uniq_id();
						$this->ser_obj[$hash] = array("is_object" => true, "objstr" => $dat, "parent" => $this->menu_hash2id[$oid]);
					}
				}
			}
		}

		return serialize($this->ser_obj);
	}

	////
	// !this should create a menu from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$dat = unserialize($str);

		$hash2id = array(0 => $parent);

		foreach($dat as $hash => $row)
		{
			if (!$row["is_object"])
			{
				$ob = $row["object"];
				unset($ob["brother_of"]);
				$ob["parent"] = $hash2id[$ob["parent"]];
				$this->quote(&$ob);
				$id = $this->new_object($ob);
				$hash2id[$hash] = $id;

				$menu = $row["table"];
				$m_ids = array("id");
				$m_vls = array($id);
				foreach($menu as $col => $val)
				{
					if ($col != "id" && $col != "rec")
					{
						$m_ids[] = $col;
						$m_vls[] = "'".$val."'";
					}
				}
				$this->db_query("INSERT INTO menu (".join(",",$m_ids).") VALUES(".join(",",$m_vls).")");
			}
			else
			{
				$this->unserialize(array("str" => $row["objstr"], "parent" => $hash2id[$row["parent"]], "period" => $period));
			}
		}
		return true;
	}
}
?>
