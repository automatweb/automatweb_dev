<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/admin_menus.aw,v 1.68 2004/06/11 08:52:42 kristo Exp $

class admin_menus extends aw_template
{
	function admin_menus()
	{
		$this->init("automatweb/menuedit");

		// FIXME: damn this is a mess
		$this->lc_load("menuedit","lc_menuedit");
		lc_site_load("menuedit",$this);
		lc_load("definition");
	}

	function get_add_menu($arr)
	{
		extract($arr);

		$this->add_menu = array();
		// check if any parent menus have config objects attached
		$atc_id = 0;

		$obj = obj($id);

		$atc_inst = get_instance("admin/add_tree_conf");
		$atc_id = $atc_inst->get_current_conf();

		$this->is_atc = false;
		if ($atc_id)
		{
			$atc_o = obj($atc_id);
			$this->visible = $atc_o->meta("visible");
			$this->usable = $atc_o->meta("usable");
			$this->is_atc = true;
		}
		return ($this->req_get_default_add_menu(0, $id, $period, 0));
	}

	function get_az_def_menu($pt, $parent, $period, $fld_id)
	{
		$cldata = array();
		$n2id = array();
		foreach($this->cfg["classes"] as $clid => $_cldata)
		{
			if (!empty($_cldata["name"]) && $_cldata["can_add"])
			{
				$cldata[$_cldata["name"][0]][$_cldata["file"]] = $_cldata["name"];
				$n2id[$_cldata["file"]] = $clid;
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
				if (!$this->is_atc || ($this->visible["obj"][$n2id[$cl_file]]))
				{
					if (!$this->is_atc || $this->usable[$n2id[$cl_file]])
					{
						$addlink = $this->mk_my_orb("new", array("parent" => $parent, "period" => $period), $cl_file, true, true);
					}
					else
					{
						$addlink = "";
					}

					$this->add_menu["letter_" . $letter]["letter_" . $letter . $cl_file] = array(
						"caption" => $cl_name,
						"link" => $addlink,
						"list" => "list",
					);
				}
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
						if (!$this->is_atc || ($this->visible["obj"][$clid]))
						{
							if (!$this->is_atc || $this->usable[$clid])
							{
								$addlink = $this->mk_my_orb("new", array("parent" => $parent, "period" => $period), $cldata["file"], true, true);
							}
							else
							{
								$addlink = "";
							}
							$tcnt++;
							$this->add_menu[0][$tcnt] = array(
								"caption" => $cldata["name"],
								"link" => $addlink,
							);
						}
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
						if (!$this->is_atc || ($this->visible["obj"][$clid]))
						{
							if (!$this->is_atc || $this->usable[$clid])
							{
								$addlink = $this->mk_my_orb("new", array("parent" => $parent, "period" => $period), $cldata["file"], true, true);
							}
							else
							{
								$addlink = "";
							}
							$this->add_menu[$fld_id]["class_" . $clid] = array(
								"caption" => $cldata["name"],
								"link" => $addlink,
							);
						}
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
					if ($this->is_atc && !$this->visible["fld"][$fid])
					{
						continue;
					}

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

		if (!is_object($obj))
		{
			$obj = obj($id);
		}

		$sep = "\n";
		if ($sharp)
		{
			$sep = "#";
		}

		$baseurl = $this->cfg["baseurl"];
		$retval = "";

		if ($obj->class_id() == CL_PSEUDO)
		{
			$ourl = $this->mk_my_orb("right_frame", array("id" => $id, "parent" => $obj->id(),"period" => $period), "admin_menus",true,true);
			$this->vars(array(
				"link" => $ourl,
				"text" => "Ava"
			));
			$retval = $this->parse("MENU_ITEM");
		}

		if ($this->can("edit", $id))
		{
			$churl = $this->mk_my_orb("change", array("id" => $id, "parent" => $obj->parent(),"period" => $period), $this->cfg["classes"][$obj->class_id()]["file"],true,true);

			$this->vars(array(
				"link" => $churl,
				"text" => "Muuda"
			));
			$retval .= $this->parse("MENU_ITEM");

			$cuturl = $this->mk_my_orb("cut", array("reforb" => 1, "id" => $id, "parent" => $obj->parent(),"sel[$id]" => "1"), "admin_menus",true,true);

			$this->vars(array(
				"link" => $cuturl,
				"text" => "L&otilde;ika"
			));
			$retval .= $this->parse("MENU_ITEM");
		}

		$copyurl = $this->mk_my_orb("copy", array("reforb" => 1, "id" => $id, "parent" => $obj->parent(),"sel[$id]" => "1","period" => $period), "admin_menus",true,true);

		$this->vars(array(
			"link" => $copyurl,
			"text" => "Kopeeri"
		));
		$retval .= $this->parse("MENU_ITEM");

		if ($this->can("delete", $id))
		{
			$delurl = $this->mk_my_orb("delete", array("reforb" => 1, "id" => $id, "parent" => $obj->parent(),"sel[$id]" => "1","period" => $period), "admin_menus",true,true);

			$delurl = "javascript:if(confirm('Kustutada valitud objektid?')){window.location='$delurl';};";

			$this->vars(array(
				"link" => $delurl,
				"text" => "Kustuta"
			));
			$retval .= $this->parse("MENU_ITEM");
		}

		/*
		$this->vars(array(
			'link' => $this->mk_my_orb('mk_shortcut', array('id' => $id), 'admin/shortcut'),
			'text' => 'Tee kiirviide',
		));
		$retval .= $this->parse("MENU_ITEM");
		*/

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

	/** shows menus importing form 
		
		@attrib name=import params=name default="0"
		
		@param parent required
		
		@returns
		
		
		@comment

	**/
	function import($arr)
	{
		extract($arr);
		$this->mk_path($parent,LC_MENUEDIT_IMPORT_MENU);
		$this->read_template("import.tpl");
		$this->vars(array("reforb" => $this->mk_reforb("submit_import", array("parent" => $parent))));
		return $this->parse();
	}

	/** does the actual menu importing bit 
		
		@attrib name=submit_import params=name default="0"
		
		@param parent required
		
		@returns
		
		
		@comment

	**/
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
	}

	function req_import_menus($i_p, &$menus, $parent)
	{
		if (!is_array($menus[$i_p]))
		{
			return;
		}

		$p_o = obj($parent);
		$mt = $p_o->prop("type");

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

	/** cuts the selected objects 
		
		@attrib name=cut params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/** copies the selected objects 
		
		@attrib name=copy params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function copy($arr)
	{
		extract($arr);

		// check if any objects that are to be copied need special handling
		if (is_array($sel))
		{
			foreach($sel as $oid => $one)
			{
				$ob = obj($oid);
				if ($ob->class_id() == CL_PSEUDO)
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

	/**  
		
		@attrib name=copy_feedback params=name default="0"
		
		@param parent required
		@param period optional
		@param sel optional
		
		@returns
		
		
		@comment

	**/
	function copy_feedback($arr)
	{
		extract($arr);
		$this->read_template("copy_feedback.tpl");
		$this->mk_path($parent, "Vali kuidas objekte kopeerida");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_copy_feedback", array("parent" => $parent, "period" => $period,"sel" => $sel))
		));

		return $this->parse();
	}

	/**  
		
		@attrib name=submit_copy_feedback params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/** pastes the cut objects 
		
		@attrib name=paste params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function paste($arr)
	{
		extract($arr);

		$cut_objects = aw_global_get("cut_objects");
		$copied_objects = aw_global_get("copied_objects");

		$cache = get_instance("cache");
		$langs = get_instance("languages");

		if (is_array($cut_objects))
		{
			reset($cut_objects);
			while (list(,$oid) = each($cut_objects))
			{
				if ($oid != $parent)
				{
					// so, let the object update itself when it is being cut-pasted, if it so desires
					$o = obj($oid);
					if ($this->cfg["classes"][$o->class_id()]["file"] != "")
					{
						$inst = $o->instance();
						if (method_exists($inst, "cut_hook"))
						{
							$inst->cut_hook(array(
								"oid" => $oid,
								"new_parent" => $parent
							));
						}
					}
					
					$cache->file_invalidate_regex("admin_menus_".$o->parent().".*");
					$o->set_parent($parent);
					if ($period)
					{
						$o->set_period($period);
					}

					$o->set_lang($langs->get_langid());
					$o->save();
				}
			}
		}
		aw_session_set("cut_objects",array());

		$conns = $obj_id_map = array();
		$msgs = array();
		if (is_array($copied_objects))
		{
			reset($copied_objects);
			while (list($oid,$str) = each($copied_objects))
			{
				$id = $this->unserialize(array("str" => $str, "parent" => $parent, "period" => $period));
				if (is_oid($id))
				{
					$obj_id_map[$oid] = $id;
					if (is_array($str["connections"]))
					{
						$conns[$id] = $str["connections"];
					};
				}
			}
		}

		// now, cycle over those and create the bloody relations and be done with it
		foreach($conns as $obj_id => $connections)
		{
			foreach($connections as $connection)
			{
				// now, create the alias?
				$obj_inst = new object($obj_id);
				$obj_inst->connect(array(
					"to" => $obj_id_map[$connection["to"]],
					"reltype" => $connection["reltype"],
				));
			}
		};

		$this->invalidate_menu_cache();

		aw_session_set("copied_objects",array());
		$_SESSION["cut_objects"] = false;
		$_SESSION["copied_objects"] = false;
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
	}

	function o_delete($arr)
	{
		extract($arr);
		if (is_array($sel))
		{
			reset($sel);
			while (list($ooid,) = each($sel))
			{
				$o = obj($ooid);
				$o->delete();
			}
		}
		if ($oid)
		{
			$o = obj($oid);
			$o->delete();
		}

		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
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
		$p_o = obj($parent);
		$ch = $p_o->path();
		foreach($ch as $o)
		{
			if ($o->meta("objtbl_conf"))
			{
				$this->co_id = $o->meta("objtbl_conf");
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

	function cache_right_frame($arr)
	{
		$params = $arr;
		$params["lang_id"] = aw_global_get("lang_id");
		$params["site_id"] = aw_ini_get("site_id");
		$params["uid"] = aw_ini_get("uid");
		$params["ft_page"] = $GLOBALS["ft_page"];
		if (empty($params["parent"]))
		{
			$params["parent"] = $this->cfg["rootmenu"];
		}
	
		$key = "admin_menus_".$params["parent"]."_".join("_", map2("%s_%s", $params));
		$ts = $this->db_fetch_field("SELECT max(modified) as mod FROM objects WHERE parent = ".$params["parent"], "mod");

		$ca = get_instance("cache");
		if (!($res = $ca->file_get_ts($key, $ts)) || ((is_array($GLOBALS["cut_objects"]) && count($GLOBALS["cut_objects"]) > 0) || (is_array($GLOBALS["copied_objects"]) && count($GLOBALS["copied_objects"]) > 0)))
		{
			$res = $this->right_frame($arr);
			$ca->file_set($key, $res);
		}
		else
		{
			$this->mk_path($params["parent"],"",$params["period"]);
		}
		return $res;

	}

	/** Displays the right frame table .. uh, what a name. 
		
		@attrib name=right_frame params=name default="0"
		
		@param parent optional
		@param period optional
		@param sortby optional
		@param sort_order optional
		@param view_type optional
		
		@returns
		
		
		@comment

	**/
	function right_frame($arr)
	{
		extract($arr);
		if (!$this->prog_acl("view", PRG_MENUEDIT))
		{
			$this->acl_error("view", PRG_MENUEDIT);
		}

		get_instance("icons");
		aw_global_set("date","");

		$lang_id = aw_global_get("lang_id");
		$site_id = $this->cfg["site_id"];
		$parent = !empty($parent) ? $parent : $this->cfg["rootmenu"];
		$menu_obj = new object($parent);
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

		$this->mk_path($parent,"&nbsp;",$period);

		$la = get_instance("languages");
		$lar = $la->get_list();

		$this->setup_rf_table($parent);

		$ps = "";

		$current_period = aw_global_get("current_period");

		if ($period)
		{
			$ps = " AND ((objects.period = '$period') OR (objects.class_id = ".CL_PSEUDO." AND objects.periodic = 1)) ";
		}
		// if no period is set in the url, BUT the menu is periodic, then only show objects from the current period
		// this fucks shit up. basically, a periodic menu can have non-periodic submenus
		// in that case there really is no way of seeing them 
		/*elseif ($menu_obj->prop("periodic") == 1 && isset($current_period))
		{
			$ps = " AND ((objects.period = '$current_period') OR (objects.class_id = ".CL_PSEUDO." AND objects.periodic = 1)) ";
		}*/
		else
		{
			$ps = " AND (period = 0 OR period IS NULL OR class_id IN (".CL_USER."))";
		};


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
				(lang_id = '$lang_id' OR m.type = ".MN_CLIENT." OR objects.class_id IN(".CL_PERIOD .",".CL_USER.",".CL_GROUP.",".CL_MSGBOARD_TOPIC.",".CL_LANGUAGE."))
				 AND
				status != 0
				$cls $ps ";

		$query = "FROM objects
				LEFT JOIN menu m ON m.id = objects.oid
			WHERE
				$where";

/*		$filter = array(
			"parent" => $parent,
			new object_list_filter(array(
				"logic" => "OR",
				"non_filter_classes" => CL_MENU,
				"conditions" => array(
					"lang_id" => $lang_id,
					"class_id" => array(CL_PERIOD, CL_USER, CL_GROUP, CL_MSGBOARD_TOPIC),
					"type" => MN_CLIENT
				)
			))
		);
		$GLOBALS["DUKE"] = 1;
		$ob = new object_list($filter);
		die();*/

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


		switch($view_type)
		{
			case 'big':
				$tpl = 'bigicons.tpl';
			break;
			case 'small':
				$tpl = 'smallicons.tpl';
			break;
			case 'detail':
				$tpl = 'js_popup_menu.tpl';
				$view_type = 'detail';
			break;
			default :
			{
				if (isset($GLOBALS['menu_last_view'][$parent]) && ($GLOBALS['menu_last_view'][$parent] != 'detail'))
				{
					$view_type = $GLOBALS['menu_last_view'][$parent];
					$tpl = $GLOBALS['menu_last_view'][$parent].'icons.tpl';
				}
				else
				{
					$tpl = 'js_popup_menu.tpl';
					$view_type = 'detail';
				}
			}
		}
		
		//if ($view_type != 'detail')
		{
			$menu_last_view = $GLOBALS['menu_last_view'];
			$menu_last_view[$parent] = $view_type;
			aw_session_set('menu_last_view',$menu_last_view);
		}
	
		$this->set_parse_method("eval");
		$this->read_template($tpl);


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
				$comment = strip_tags($row["comment"]);
				$row["is_menu"] = 1;
			}
			else
			if ($row["class_id"] == CL_PLANNER)
			{
				$chlink = $this->mk_my_orb("change",array("id" => $row["oid"]),"planner");
			}
			else
			{
				$chlink = $this->mk_my_orb("change", array("id" => $row["oid"], "period" => $period),$this->cfg["classes"][$row["class_id"]]["file"]);
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

			$row["lang_id"] = $lar[$row["lang_id"]];

			$this->save_handle();

			if ($view_type == 'detail')
			{
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
			}
			elseif($view_type == 'big')
			{
				$this->vars(array(
					"MENU_ITEM" => $this->get_popup_data(array(
						"period" => $period,
						"id" => $row["oid"],
						"ret_data" => true,
						"sharp" => true,
						"type" => "js",
						"obj" => $row
					))
				));
			}


			$this->restore_handle();
			
			$iu = icons::get_icon_url($row["class_id"],$row["name"]);
			$row["icon"] = '<img alt="'.$comment.'" src="'.$iu.'">';
			$this->t->set_default_sortby("name");
			$caption = ($row["name"] == '' ? "(nimeta)" : $row["name"]);

			$row["name"] = '<a href="'.$chlink.'" title="'.$comment.'">'.$caption."</a>";

			if ($row["class_id"] == CL_SHORTCUT)
			{
				$row["class_id"] = '(shortcut)';
			}
			else
			{
				$row["class_id"] = $this->cfg["classes"][$row["class_id"]]["name"];
			}
						
						
			$row["link"] = "<a href=\"".$this->cfg["baseurl"]."/".$row["oid"]."\">Link</a>";
			
			
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
			
			if ($this->co_id)
			{
				$this->otc_inst->table_row($row, &$this->t);
			}
			else
			{
				$this->t->define_data($row);
			}

			//axel h�kkis,
			$row['icon_url'] = $iu;
			$row['caption'] = $caption;
			$row['chlink'] = $chlink;

			$row["comment"] = strip_tags($row["comment"]);


			$this->vars($row);
			$the_icons .= $this->parse('ICON');

			$num_records++;

		}

		$this->get_add_menu(array(
			"id" => $parent,
			"ret_data" => true,
			"sharp" => true,
			"addmenu" => 1,
			"period" => empty($period) && $menu_obj->prop("periodic") == 1 ? $current_period : $period,
		));

		$this->read_template("js_add_menu.tpl");

		$whole_menu = "";


		foreach($this->add_menu as $item_id => $item_collection)
		{
			$menu_data = "";
			foreach($item_collection as $el_id => $el_data)
			{
				//if this el_id has children, make it a submenu
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
					if ($el_data["link"] != "")
					{
						$tpl = "MENU_ITEM";
					}	
					else
					{
						$tpl = "MENU_ITEM_SHOW";
					}
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

		$view_types = array(
			'big' => array('caption' => 'Suured ikoonid','title' => ''),
			'small' => array('caption' => 'V�iksed ikoonid','title' => ''),
			'detail' => array('caption' => 'Detailne vaade','title' => ''),
		);

		$items = '';
		foreach($view_types as $key => $val)
		{
			$this->vars(array(
				'caption' => $val['caption'],
				'title' => $val['title'],
				'url' => preg_replace('/&view_type=[^&$]*/','',aw_global_get('REQUEST_URI')).'&view_type='.$key,
			));
			$items .= $this->parse('MENU_ITEM');
		}

		$this->vars(array(
			'items' => $items,
			'menu_id' => 'view_type',
		));
		$view_button_menu = $this->parse('MENU2');

		$la = get_instance("languages");

		if (!$sortby)
		{
			$sortby = "hidden_jrk";
		};

		if ($sortby == "jrk")
		{
			$sortby = "hidden_jrk";
		};

		if (!$GLOBALS["sort_order"])
		{
			$GLOBALS["sort_order"] = "asc";
		};

		$this->t->set_numeric_field("hidden_jrk");

		$this->t->sort_by(array(
			"field" => array("is_menu", $sortby, "name"),
			"sorder" => array("is_menu" => "desc", $sortby => $GLOBALS["sort_order"], "name" => "asc")
		));

		$this->read_template("right_frame.tpl");

		$toolbar = $this->rf_toolbar(array(
			"parent" => $parent,
			"period" => $period,
			"add_applet" => $whole_menu,
			"sel_count" => count($sel_objs),
		));

		$toolbar_data = $toolbar->get_toolbar();
		$toolbar_data .= $whole_menu;
		$toolbar_data .= $view_button_menu;


		$icons = (($view_type == 'big') || ($view_type == 'small'));

		$this->vars(array(
			'viewstyle' => $icons ? 'awmenuedittablerow' : 'awmenuedittableborder',
			"table" => $pageselector.($icons ? $the_icons : $this->t->draw()),
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
				"class" => "menuButton",
			));
		};

		if (empty($no_save))
		{
			$toolbar->add_button(array(
				"name" => "save",
				"tooltip" => "Salvesta",
				"url" => "javascript:document.foo.submit()",
				"img" => "save.gif",
			));
		};
		
		$toolbar->add_button(array(
			"name" => "search",
			"tooltip" => "Otsi",
			"url" => $this->mk_my_orb("search",array("parent" => $parent),"search"),
			"img" => "search.gif",
		));
		

		$toolbar->add_separator();

		$toolbar->add_button(array(
			"name" => "cut",
			"tooltip" => "L&otilde;ika",
			"url" => "javascript:submit('cut')",
			"img" => "cut.gif",
		));

		$toolbar->add_button(array(
			"name" => "copy",
			"tooltip" => "Kopeeri",
			"url" => "javascript:submit('copy')",
			"img" => "copy.gif",
		));

		if ($sel_count > 0)
		{
			$toolbar->add_button(array(
				"name" => "paste",
				"tooltip" => "Kleebi",
				"url" => "javascript:submit('paste')",
				"img" => "paste.gif",
			));
		};

		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => "Kustuta",
			"url" => "javascript:if(confirm('Kustutada valitud objektid?')){submit('delete')};",
			"img" => "delete.gif",
		));

		$toolbar->add_button(array(
			"name" => "edit",
			"tooltip" => "Muuda",
			"url" => "javascript:change()",
			"img" => "edit.gif",
		));
		
		$toolbar->add_separator();
	
		$toolbar->add_button(array(
			"name" => "refresh",
			"tooltip" => "Uuenda",
			"url" => "javascript:window.location.reload()",
			"img" => "refresh.gif",
		));
	
		$toolbar->add_button(array(
			"name" => "import",
			"tooltip" => "Impordi",
			"url" => $this->mk_my_orb("import",array("parent" => $parent)),
			"img" => "import.gif",
		));

		$toolbar->add_button(array(
			"name" => "view_type",
			"tooltip" => "detailne/Ikooni vaade",
			"img" => "preview.gif",
			"url" => "#",
			"onClick" => "return buttonClick(event, 'view_type');",
			"class" => "menuButton",
		));


		if (isset($callback) && is_array($callback) && sizeof($callback) == 2)
		{
			$callback[0]->$callback[1](array("toolbar" => &$toolbar));
		};

		return $toolbar;
	}

	/**  
		
		@attrib name=submit_rf params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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
						if ($this->can("edit", $oid) && $this->can("view", $oid))
						{
							$o = obj($oid);
							if ($column == "jrk")
							{
								$o->set_ord((int)$val);
							}
							else
							{
								$o->set_prop($column, $val);
							}
							$o->save();
						}
					}
				}
			}
		}
		$this->invalidate_menu_cache();
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period, "sortby" => $sortby, "sort_order" => $sort_order));
	}

	/**  
		
		@attrib name=delete params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function new_delete($arr)
	{
		extract($arr);
		if (is_array($sel))
		{
			$ol = new object_list(array(
				"oid" => array_keys($sel),
				"site_id" => array(),
				"lang_id" => array()
			));
			for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				if ($this->can("delete", $o->id()))
				{
					$o->delete();
				}
			}
		}
		$this->invalidate_menu_cache();
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period, "sortby" => $sortby, "sort_order" => $sort_order));
	}

	/**  
		
		@attrib name=change_redir params=name default="0"
		
	
		@returns
		
		
		@comment

	**/
	function change_redir($arr)
	{
		extract($arr);
		if (!is_array($sel))
		{
			$this->raise_error(ERR_MNED_NO_OBJS,"Valitud pole &uuml;htegi objekti!", true);
		}

		reset($sel);
		list($oid,) = each($sel);

		$obj = obj($oid);
		return $this->mk_my_orb("change", array("id" => $oid, "parent" => $parent), $obj->class_id());
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
		$od = obj($oid);
		$od = $od->fetch();
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
