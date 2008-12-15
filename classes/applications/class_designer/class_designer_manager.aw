<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/class_designer_manager.aw,v 1.16 2008/12/15 13:06:28 kristo Exp $
// class_designer_manager.aw - Klasside brauser 
/*

@classinfo syslog_type=ST_CLASS_DESIGNER_MANAGER relationmgr=yes no_comment=1 no_status=1 maintainer=kristo prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@default group=mgr

	@property mgr_tb type=toolbar no_caption=1

	@layout mgr_hbox type=hbox width=20%:80%

	@property mgr_tree type=treeview no_caption=1 parent=mgr_hbox
	@property mgr_tbl type=table no_caption=1 parent=mgr_hbox

@default group=rels

	@property rels_tb type=toolbar no_caption=1

	@layout rels_hbox type=hbox width=20%:80%

	@property rels_tree type=treeview no_caption=1 parent=rels_hbox
	@property rels_tbl type=table no_caption=1 parent=rels_hbox

@default group=classes

	@property toolbar type=toolbar no_caption=1 store=no
	
	@layout classes_split type=hbox

		@layout classes_left type=vbox parent=classes_split

			@layout classes_tree type=vbox area_caption=Klasside&nbsp;puu closeable=1 parent=classes_left
	
				@property classes_tree type=treeview no_caption=1 parent=classes_tree store=no

			@layout classes_groups type=vbox area_caption=Klasside&nbsp;grupid closeable=1 parent=classes_left
	
				@property classes_groups type=treeview no_caption=1 parent=classes_groups store=no

		@property classes_list type=table no_caption=1 store=no parent=classes_split


@default group=cl_usage_stats_clids

	@layout cl_usage_stats_split type=hbox width=20%:80%

		@layout cl_usage_stats_left type=vbox parent=cl_usage_stats_split

			@layout cl_usage_stats_tree type=vbox area_caption=Klasside&nbsp;puu closeable=1 parent=cl_usage_stats_left
	
				@property cl_usage_stats_tree type=treeview no_caption=1 parent=cl_usage_stats_tree store=no

			@layout cl_usage_stats_groups type=vbox area_caption=Klasside&nbsp;grupid closeable=1 parent=cl_usage_stats_left
	
				@property cl_usage_stats_groups type=treeview no_caption=1 parent=cl_usage_stats_groups store=no

		@layout cl_usage_stats_right type=vbox parent=cl_usage_stats_split

			@layout cl_usage_stats_right_split type=hbox parent=cl_usage_stats_right

				@property cl_usage_stats_list type=table no_caption=1 store=no parent=cl_usage_stats_right_split
				@property site_usage_stats_list type=table no_caption=1 store=no parent=cl_usage_stats_right_split

@default group=cl_usage_stats_props

	@layout cl_usage_props_stats_split type=hbox width=20%:80%

		@layout cl_usage_props_stats_left type=vbox parent=cl_usage_props_stats_split

			@layout cl_usage_props_stats_tree type=vbox area_caption=Klasside&nbsp;puu closeable=1 parent=cl_usage_props_stats_left
	
				@property cl_usage_props_stats_tree type=treeview no_caption=1 parent=cl_usage_props_stats_tree store=no

			@layout cl_usage_props_stats_groups type=vbox area_caption=Klasside&nbsp;grupid closeable=1 parent=cl_usage_props_stats_left
	
				@property cl_usage_props_stats_groups type=treeview no_caption=1 parent=cl_usage_props_stats_groups store=no

		@layout cl_usage_props_stats_right type=vbox parent=cl_usage_props_stats_split

			@layout cl_usage_props_stats_right_split type=hbox parent=cl_usage_props_stats_right

				@property cl_usage_props_stats_list type=table no_caption=1 store=no parent=cl_usage_props_stats_right_split

@default group=cl_usage_stats_tms

	@layout cl_usage_stats_tms_split type=hbox width=20%:80%

		@layout cl_usage_stats_tms_left type=vbox parent=cl_usage_stats_tms_split

			@layout cl_usage_stats_tms_tree type=vbox area_caption=Saidid closeable=1 parent=cl_usage_stats_tms_left
	
				@property cl_usage_stats_tms_tree type=treeview no_caption=1 parent=cl_usage_stats_tms_tree store=no

		@layout cl_usage_stats_tms_right type=vbox parent=cl_usage_stats_tms_split

			@layout cl_usage_stats_tms_right_split type=hbox parent=cl_usage_stats_tms_right

				@property cl_usage_stats_tms_list type=table no_caption=1 store=no parent=cl_usage_stats_tms_right_split


@groupinfo mgr caption="Manager" submit=no
@groupinfo rels caption="Seosed" submit=no
@groupinfo classes caption="Omadused"
@groupinfo cl_usage_stats caption="Statistika"
	@groupinfo cl_usage_stats_clids caption="Klasside TOP" parent=cl_usage_stats submit=no
	@groupinfo cl_usage_stats_props caption="Omadused" parent=cl_usage_stats submit=no
	@groupinfo cl_usage_stats_tms caption="Ajad" parent=cl_usage_stats submit=no
*/

class class_designer_manager extends class_base
{
	function class_designer_manager()
	{
		$this->init(array(
			"tpldir" => "applications/class_designer/class_designer_manager",
			"clid" => CL_CLASS_DESIGNER_MANAGER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "mgr_tb":
				$this->_mgr_tb($arr);
				break;

			case "mgr_tree":
				$this->_mgr_tree($arr);
				break;

			case "mgr_tbl":
				$this->_mgr_tbl($arr);
				break;

			case "rels_tb":
				$this->_rels_tb($arr);
				break;

			case "rels_tree":
				$this->_mgr_tree($arr);
				break;

			case "rels_tbl":
				$this->_rels_tbl($arr);
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
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["tf"] = ifset($_REQUEST, "tf");
		$arr["classf_name"] = "";
		$arr["ch_classf_name"] = "";
		$arr["ch_classf_id"] = "";
	}

	function _mgr_tb($arr)
	{
		$t =& $arr["prop"]["toolbar"];

		$t->add_menu_button(array(
			"name" => "new",
			"img" => "new.gif",
		));

		$t->add_menu_item(array(
			"parent" => "new",
			"text" => t("Lisa klass"),
			"link" => html::get_new_url(
				CL_CLASS_DESIGNER, 
				$arr["obj_inst"]->id(), 
				array(
					"return_url" => get_ru(),
					"register_under" => $_GET["tf"]
				)
			)
		));

		$t->add_menu_item(array(
			"parent" => "new",
			"text" => t("Lisa perekond"),
			"action" => "create_clf",
			"onClick" => "document.changeform.classf_name.value=prompt('Sisesta nimi', ' ');"
		));

		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"caption" => t('Kustuta'),
			"action" => "delete_p",
		));

		$t->add_separator();
		$t->add_button(array(
			"name" => "cut",
			"img" => "cut.gif",
			"caption" => t('L&otilde;ika'),
			"action" => "cut_p",
		));

		$t->add_button(array(
			"name" => "copy",
			"img" => "copy.gif",
			"caption" => t('Kopeeri'),
			"action" => "copy_p",
		));

		$has_cut = count(safe_array($_SESSION["class_designer"]["cut_classes"])) +
				   count(safe_array($_SESSION["class_designer"]["cut_folders"]));
		$has_cop = count(safe_array($_SESSION["class_designer"]["copied_classes"])) + 
				   count(safe_array($_SESSION["class_designer"]["copied_folders"]));
		if ($has_cut || $has_cop)
		{
			$t->add_button(array(
				"name" => "paste",
				"img" => "paste.gif",
				"caption" => t('Kleebi'),
				"action" => "paste_p",
			));
		}
	}

	function _mgr_tree($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];

		$t->start_tree(array(
			"tree_id" => "class_mgr_tree",
			"persist_state" => true,
			"type" => TREE_DHTML
		));

		$clsf = aw_ini_get("classfolders");
		foreach($clsf as $id => $inf)
		{
			$t->add_item((int)$inf["parent"], array(
				"name" => $arr["request"]["tf"] == $id ? "<b>".$inf["name"]."</b>" : $inf["name"],
				"id" => $id,
				"url" => aw_url_change_var("tf", $id)
			));
		}
	}

	function _init_mgr_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "icon",
			"caption" => t(""),
			"align" => "center",
			"width" => 1
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "clid_nm",
			"caption" => t("ID"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "size",
			"caption" => t("Suurus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "menu",
			"caption" => t("Tegevus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "sel",
			"caption" => t("Vali"),
			"width" => 1,
			"align" => "center"
		));
	}

	function _mgr_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_mgr_tbl($t);

		$ol = new object_list(array(
			"class_id" => CL_CLASS_DESIGNER,
			"lang_id" => array(),
			"site_id" => array()
		));
		$designed = array();
		foreach($ol->arr() as $designer)
		{
			$designed[$designer->prop("reg_class_id")] = $designer->id();
		}
		
		$tf = $arr["request"]["tf"];

		$clf = aw_ini_get("classfolders");
		foreach($clf as $clf => $dat)
		{
			if ((int)$dat["parent"] != (int)$tf)
			{
				continue;
			}

			$sel = html::checkbox(array(
				"name" => "sel_fld[]",
				"value" => $clf
			));

			$t->define_data(array(
				"name" => $dat["name"],
				"add" => "",
				"design" => "",
				"clid" => "",
				"size" => $this->get_clf_size($clf),
				"icon" => html::img(array(
					"url" => aw_ini_get("icons.server")."/class_1.gif"
				)),
				"sel" => $sel,
				"menu" => $this->_get_menu("fld", $clf, NULL, $dat["name"])
			));
		}			

		$clss = aw_ini_get("classes");
		foreach($clss as $clid => $cld)
		{
			$show = false;
			if ($cld["parents"] == "" && !$tf)
			{
				$show = true;
			}
			else
			{
				$parents = $this->make_keys(explode(",", $cld["parents"]));
				if ($parents[$tf])
				{
					$show = true;
				}
			}

			if (!$show)
			{
				continue;
			}

			$design = "";
			if ($designed[$clid])
			{
				$design = html::get_change_url($designed[$clid], array("return_url" => get_ru()), "Disaini");
			}


			$sel = html::checkbox(array(
				"name" => "sel[]",
				"value" => $clid
			));

			$t->define_data(array(
				"name" => $cld["name"],
				"design" => $design,
				"clid_nm" => $cld["def"],
				"size" => $this->get_class_size($cld["file"]),
				"icon" => html::img(array(
					"url" => aw_ini_get("icons.server")."/class_".$clid.".gif"
				)),
				"sel" => $sel,
				"menu" => $this->_get_menu("cls", $clid, $designed[$clid], $cld["name"], $arr["obj_inst"]->id())
			));
		}
		$t->set_sortable(false);
	}

	function _rels_tb($arr)
	{
		$t =& $arr["prop"]["toolbar"];

		$t->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"caption" => t('Lisa'),
			"url" => html::get_new_url(
				CL_CLASS_DESIGNER, 
				$arr["obj_inst"]->id(), 
				array(
					"return_url" => get_ru(),
					"register_under" => $_GET["tf"]
				)
			)
		));
	}

	function _init_rels_tree(&$t)
	{
		$t->define_field(array(
			"name" => "class_name",
			"caption" => t("Klassi nimi"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "rel_name",
			"caption" => t("Seose nimi"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "rel_to",
			"caption" => t("Seos klassiga"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "sel",
			"caption" => t("Vali"),
			"align" => "center",
		));
	}

	function _rels_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_rels_tree($t);

		$ol = new object_list(array(
			"class_id" => CL_CLASS_DESIGNER,
			"lang_id" => array(),
			"site_id" => array()
		));
		$designed = array();
		foreach($ol->arr() as $designer)
		{
			$designed[$designer->prop("reg_class_id")] = $designer->id();
		}

		$tf = $arr["request"]["tf"];
		$clss = aw_ini_get("classes");
		foreach($clss as $clid => $cld)
		{
			$show = false;
			if ($cld["parents"] == "" && !$tf)
			{
				$show = true;
			}
			else
			{
				$parents = $this->make_keys(explode(",", $cld["parents"]));
				if ($parents[$tf])
				{
					$show = true;
				}
			}

			if (!$show)
			{
				continue;
			}

			$sel = "";
			if ($designed[$clid])
			{
				$sel = html::get_change_url(
					$designed[$clid], 
					array(
						"return_url" => get_ru(),
						"group" => "relations",
						"relations_mgr" => "new"
					),
					t("Lisa seos")
				);
			}

			$t->define_data(array(
				"class_name" => $cld["name"],
				"rel_name" => "",
				"rel_to" => "",
				"sel" => $sel
			));

			// rels for class
			if ($designed[$clid])
			{
				$rels = array();
				$d_o = obj($designed[$clid]);
				foreach($d_o->connections_from(array("reltype" => "RELTYPE_RELATION")) as $c)
				{
					$rel_o = $c->to();
					$rels[] = array(
						"caption" => $rel_o->name(),
						"clid" => $rel_o->prop("r_class_id")
					);
				}
			}
			else
			{
				$cu = get_instance("cfg/cfgutils");
				$ps = $cu->load_properties(array("clid" => $clid, "file" => basename($cld["file"])));
				$rels = $cu->get_relinfo();
			}
			foreach($rels as $rel)
			{
				$rel_to = array();
				foreach(safe_array($rel["clid"]) as $r_clid)
				{
					$rel_to[] = $clss[$r_clid]["name"];
				}

				$t->define_data(array(
					"class_name" => "",
					"rel_name" => $rel["caption"],
					"rel_to" => join(", ", $rel_to),
					"sel" => ""
				));
			}
		}
		$t->set_sortable(false);
	}

	function get_class_size($fn)
	{
		$fqfn = aw_ini_get("classdir")."/".$fn.".".aw_ini_get("ext");
		return number_format(filesize($fqfn) / 1024, 2)." kb / ".count(file($fqfn))." rida";
	}

	/**

		@attrib name=check_add_object

		@param id required type=int acl=view
		@param clid required type=int
		@param ru optional
	**/
	function check_add_object($arr)
	{
		$parent = $this->_get_obj_parent_by_clid($arr["id"], $arr["clid"]);
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => $arr["clid"],
		));

		if (!$ol->count())
		{
			return html::get_new_url($arr["clid"], $parent, array("return_url" => ($arr["ru"])));
		}
		else
		{
			$o = $ol->begin();
			return html::get_change_url($o->id(), array("return_url" => ($arr["ru"])));
		}
	}

	function _get_class_path_in_tree($clid)
	{
		$d = aw_ini_get("classes");
		$inf = $d[$clid];
		$pts = explode(",", $inf["parents"]);
		
		$fld = $pts[0];

		$pt = array();
		$this->_req_get_pt_in_t($fld, $pt);
		return array_reverse($pt);
	}

	function _req_get_pt_in_t($fld, &$a)
	{
		$d = aw_ini_get("classfolders");
		$a[] = $d[$fld];

		if ($d[$fld]["parent"])
		{
			$this->_req_get_pt_in_t($d[$fld]["parent"], $a);
		}
	}

	/**

		@attrib name=cut_p
	
		@param sel optional 
		@param sel_fld optional
		@param post_ru required
	**/
	function cut_p($arr)
	{
		$_SESSION["class_designer"]["cut_classes"] = safe_array($arr["sel"]);
		$_SESSION["class_designer"]["cut_folders"] = safe_array($arr["sel_fld"]);
		return $arr["post_ru"];
	}

	/**

		@attrib name=copy_p

		@param sel optional 
		@param sel_fld optional
		@param post_ru required
	**/
	function copy_p($arr)
	{
		$_SESSION["class_designer"]["copied_classes"] = safe_array($arr["sel"]);
		$_SESSION["class_designer"]["copied_folders"] = safe_array($arr["sel_fld"]);
		return $arr["post_ru"];
	}

	/**

		@attrib name=paste_p

	**/
	function paste_p($arr)
	{
		$clss = aw_ini_get("classes");


		$cut = safe_array($_SESSION["class_designer"]["cut_classes"]);
		foreach($cut as $clid)
		{
			//$this->_set_ini_file_value(aw_ini_get("basedir")."/config/ini/classes.ini", "classes[$clid][parents]", $arr["tf"]);
			//$this->_set_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classes[$clid][parents]", $arr["tf"]);
			$this->_set_ini_file_value(aw_ini_get("site_basedir")."/files/class_designer_cls.ini", "classes[$clid][parents]", $arr["tf"]);
		}
		$_SESSION["class_designer"]["cut_classes"] = array();

		$cut = safe_array($_SESSION["class_designer"]["cut_folders"]);
		foreach($cut as $fld)
		{
			//$this->_set_ini_file_value(aw_ini_get("basedir")."/config/ini/classfolders.ini", "classfolders[$fld][parent]", $arr["tf"]);
			//$this->_set_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classfolders[$fld][parent]", $arr["tf"]);
			$this->_set_ini_file_value(aw_ini_get("site_basedir")."/files/class_designer_clsfld.ini", "classfolders[$fld][parent]", $arr["tf"]);
		}
		$_SESSION["class_designer"]["cut_folders"] = array();

		$cop = safe_array($_SESSION["class_designer"]["copied_classes"]);
		foreach($cop as $clid)
		{
			$np = $arr["tf"];
			if ($clss[$clid]["parents"] != "")
			{
				$curp = $this->make_keys(explode(",", $clss[$clid]["parents"]));
				$curp[$arr["tf"]] = $arr["tf"];
				$np = join(",", array_values($curp));
			}
			//$this->_set_ini_file_value(aw_ini_get("basedir")."/config/ini/classes.ini", "classes[$clid][parents]", $np);
			//$this->_set_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classes[$clid][parents]", $np);
			$this->_set_ini_file_value(aw_ini_get("site_basedir")."/files/class_designer_cls.ini", "classes[$clid][parents]", $np);
		}
		$_SESSION["class_designer"]["copied_classes"] = array();

		$flds = aw_ini_get("classfolders");
		$cop = safe_array($_SESSION["class_designer"]["copied_folders"]);
		foreach($cop as $clid)
		{
			$max_fld = max(array_keys($flds))+1;
			$np = $arr["tf"];
			$ds = $flds[$clid];

			//$this->_add_ini_file_value(aw_ini_get("basedir")."/config/ini/classfolders.ini", "classfolders[$max_fld][name]", $ds["name"]);
			//$this->_add_ini_file_value(aw_ini_get("basedir")."/config/ini/classfolders.ini", "classfolders[$max_fld][parent]", $np);
			$this->_set_ini_file_value(aw_ini_get("site_basedir")."/files/class_designer_clsfld.ini", "classfolders[$max_fld][name]", $ds["name"]);
			$this->_set_ini_file_value(aw_ini_get("site_basedir")."/files/class_designer_clsfld.ini", "classfolders[$max_fld][parent]", $np);

			//$this->_add_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classfolders[$max_fld][name]", $ds["name"]);
			//$this->_add_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classfolders[$max_fld][parent]", $np);
		}
		$_SESSION["class_designer"]["copied_folders"] = array();

		return $arr["post_ru"];
	}

	function _add_ini_file_value($file, $k, $v)
	{
		$ls = file($file);
		$ls[] = $k." = ".$v."\n";
		
		$this->put_file(array(
			"file" => $file,
			"content" => join("", $ls)
		));
	}

	function _set_ini_file_value($file, $k, $v)
	{
		$ls = file($file);
		foreach($ls as $key => $line)
		{
			if (substr($line, 0, strlen($k)) == $k)
			{
				$ls[$key] = $k." = ".$v."\n";
				$found = true;
			}
		}
		if(!$found)
		{
			$ls[] = $k." = ".$v."\n";
		}
		$this->put_file(array(
			"file" => $file,
			"content" => join("", $ls)
		));
	}

	function _del_ini_file_value($file, $k)
	{
		$ls = file($file);
		foreach($ls as $key => $line)
		{
			if (substr($line, 0, strlen($k)) == $k)
			{
				unset($ls[$key]);
			}
		}
		$this->put_file(array(
			"file" => $file,
			"content" => join("", $ls)
		));
	}

	function get_clf_size($clf)
	{
		$clss = array();
		$this->_get_classes_below($clf, $clss);

		$bytes = $lines = 0;
		foreach($clss as $cld)
		{
			$fqfn = aw_ini_get("classdir")."/".$cld["file"].".".aw_ini_get("ext");
			$bytes += filesize($fqfn);
			$lines += count(file($fqfn));
		}

		return number_format($bytes / 1024, 2)." kb / ".$lines." rida / ".count($clss)." klassi";
	}

	function _get_classes_below($clf, &$arr)
	{
		$fld = aw_ini_get("classfolders");
		foreach($fld as $id => $d)
		{
			if ($d["parent"] == $clf)
			{
				$this->_get_classes_below($id, $arr);
			}
		}

		$c = aw_ini_get("classes");
		foreach($c as $id => $d)
		{
			if (in_array($clf, explode(",", $d["parents"])))
			{
				$arr[] = $d;
			}
		}
	}

	/**

		@attrib name=create_clf

	**/
	function create_clf($arr)
	{
		$flds = aw_ini_get("classfolders");
		$max_fld = max(array_keys($flds))+1;

		//$this->_add_ini_file_value(aw_ini_get("basedir")."/config/ini/classfolders.ini", "classfolders[$max_fld][name]", $arr["classf_name"]);
		//$this->_add_ini_file_value(aw_ini_get("basedir")."/config/ini/classfolders.ini", "classfolders[$max_fld][parent]", $arr["tf"]);
		$this->_add_ini_file_value(aw_ini_get("site_basedir")."/files/class_designer_clsfld.ini", "classfolders[$max_fld][name]", $arr["classf_name"]);
		$this->_add_ini_file_value(aw_ini_get("site_basedir")."/files/class_designer_clsfld.ini", "classfolders[$max_fld][parent]", $arr["tf"]);

		//$this->_add_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classfolders[$max_fld][name]", $arr["classf_name"]);
		//$this->_add_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classfolders[$max_fld][parent]", $arr["tf"]);
		
		return $arr["post_ru"];
	}

	function _get_menu($tp, $id, $designer = NULL, $nm = NULL, $obj_id = NULL)
	{
		$this->tpl_init("automatweb/menuedit");
		$this->read_template("js_popup_menu.tpl");

		$this->vars(array(
			"menu_id" => $tp.$id,
			"menu_icon" => $this->cfg["baseurl"]."/automatweb/images/blue/obj_settings.gif",
		));
	
		if ($tp == "fld")
		{
			$items = array(
				"javascript:submit_changeform('change_clf_name',document.changeform.ch_classf_name.value=prompt('Sisesta nimi','$nm'),document.changeform.ch_classf_id.value=$id)" => t("Muuda nime"),
				$this->mk_my_orb("cut_p", array("sel_fld" => array($id), "post_ru" => get_ru())) => t("L&otilde;ika"),
				$this->mk_my_orb("copy_p", array("sel_fld" => array($id), "post_ru" => get_ru())) => t("Kopeeri"),
				$this->mk_my_orb("delete_p", array("sel_fld" => array($id), "post_ru" => get_ru())) => t("Kustuta")
			);
		}
		else
		{
			$add_link = $this->mk_my_orb(
				"check_add_object",
				array(
					"clid" => $id,
					"id" => $obj_id,
					"ru" => get_ru()
				)
			);

			$items = array(
				$add_link => t("Demo objekt"),
				"javascript:submit_changeform('change_class_name',document.changeform.ch_classf_name.value=prompt('Sisesta nimi','$nm'),document.changeform.ch_classf_id.value=$id)" => t("Muuda nime"),
			);
			if ($designer)
			{
				$items[html::get_change_url($designer, array("return_url" => get_ru()))] = t("Disaini");
			}
			else
			{
				$items[$this->mk_my_orb("create_designer_from_class", array("id" => $obj_id, "ru" => get_ru(), "clid" => $id))] = t("Loo disainer");
			}
			$items[$this->mk_my_orb("cut_p", array("sel" => array($id), "post_ru" => get_ru()))] = t("L&otilde;ika");
			$items[$this->mk_my_orb("copy_p", array("sel" => array($id), "post_ru" => get_ru()))] = t("Kopeeri");
			$items[$this->mk_my_orb("delete_p", array("sel" => array($id), "post_ru" => get_ru()))] = t("Kustuta");
		}

		$mi = "";
		foreach($items as $url => $txt)
		{
			$this->vars(array(
				"link" => $url,
				"text" => $txt
			));
			$mi .= $this->parse("MENU_ITEM");
		}

		$this->vars(array(
			"MENU_ITEM" => $mi
		));
		return $this->parse();
	}

	/**

		@attrib name=change_clf_name

	**/
	function change_clf_name($arr)
	{
		//$this->_set_ini_file_value(aw_ini_get("basedir")."/config/ini/classfolders.ini", "classfolders[$arr[ch_classf_id]][name]", $arr["ch_classf_name"]);
		//$this->_set_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classfolders[$arr[ch_classf_id]][name]", $arr["ch_classf_name"]);
		$this->_set_ini_file_value(aw_ini_get("site_basedir")."/files/class_designer_clsfld.ini", "classfolders[$arr[ch_classf_id]][name]", $arr["ch_classf_name"]);
		return $arr["post_ru"];
	}

	/**

		@attrib name=change_class_name

	**/
	function change_class_name($arr)
	{
		//$this->_set_ini_file_value(aw_ini_get("basedir")."/config/ini/classes.ini", "classes[$arr[ch_classf_id]][name]", $arr["ch_classf_name"]);
		//$this->_set_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classes[$arr[ch_classf_id]][name]", $arr["ch_classf_name"]);
		$this->_set_ini_file_value(aw_ini_get("site_basedir")."/files/class_designer_clsfld.ini", "classes[$arr[ch_classf_id]][name]", $arr["ch_classf_name"]);
		return $arr["post_ru"];
	}

	/**

		@attrib name=delete_p

		@param sel optional 
		@param sel_fld optional
		@param post_ru required
	**/
	function delete_p($arr)
	{
		$inif1 = aw_ini_get("basedir")."/config/ini/classfolders.ini";
		$inif2 = aw_ini_get("basedir")."/aw.ini";
		$inif3 = aw_ini_get("site_basedir")."/files/class_designer_clsfld.ini";
		foreach(safe_array($arr["sel_fld"]) as $fld_id)
		{
			//$this->_del_ini_file_value($inif1, "classfolders[$fld_id]");
			//$this->_del_ini_file_value($inif2, "classfolders[$fld_id]");
			
			//$this->_del_ini_file_value($inif3, "classfolders[$fld_id]");
			$this->_set_ini_file_value($inif3, "classfolders[$fld_id]", "__delete");
		}

		//$inif1 = aw_ini_get("basedir")."/config/ini/classes.ini";
		//$inif2 = aw_ini_get("basedir")."/aw.ini";
		$inif3 = aw_ini_get("site_basedir")."/files/class_designer_cls.ini";
		foreach(safe_array($arr["sel"]) as $fld_id)
		{
			//$this->_del_ini_file_value($inif1, "classes[$fld_id]");
			//$this->_del_ini_file_value($inif2, "classes[$fld_id]");

			//$this->_del_ini_file_value($inif3, "classes[$fld_id]");
			$this->_set_ini_file_value($inif3, "classes[$fld_id]", "__delete");
		}
		return $arr["post_ru"];
	}

	/** 

		@attrib name=create_designer_from_class

		@param id required type=int acl=view
		@param clid required type=int
		@param ru required
	**/
	function create_designer_from_class($arr)
	{
		$parent = $this->_get_obj_parent_by_clid($arr["id"], $arr["clid"]);

		$clss = aw_ini_get("classes");
		$o = $this->_get_object_by_parent_type_name($parent, CL_CLASS_DESIGNER, $clss[$arr["clid"]]["name"]);
		$o->set_prop("is_registered", 1);
		$o->set_prop("reg_class_id", $arr["clid"]);
		$o->set_prop("can_add", $clss[$arr["clid"]]["can_add"]);
		$o->set_prop("class_folder", $clss[$arr["clid"]]["parents"]);
		$o->save();

		$this->_parse_designer_from_class($o, $arr["clid"], $clss[$arr["clid"]]);


		return html::get_change_url($o->id(), array("return_url" => ($arr["ru"])));
	}

	function _get_obj_parent_by_clid($id, $clid)
	{
		$pt = $this->_get_class_path_in_tree($clid);
		$o = obj($id);
		foreach($pt as $inf)
		{
			$filt = array(
				"parent" => $o->id(),
				"class_id" => CL_MENU,
				"name" => $inf["name"],
				"lang_id" => array(),
				"site_id" => array()
			);
			$ol = new object_list($filt);
			if (!$ol->count())
			{
				$_pt = $o->id();
				$o = obj();
				$o->set_parent($_pt);
				$o->set_class_id(CL_MENU);
				$o->set_name($inf["name"]);
				$o->save();
			}
			else
			{
				$o = $ol->begin();
			}
		}

		return $o->id();
	}

	function _parse_designer_from_class($designer, $clid, $cld)
	{
		$cu = get_instance("cfg/cfgutils");
		$ps = $cu->load_properties(array("clid" => $clid));
		$gp = $cu->get_groupinfo();
		$ci = $cu->get_classinfo();
		$designer->set_prop("relationmgr", ($ci["relationmgr"] == "yes" ? 1 : 0));
		$designer->set_prop("no_comment", ($ci["no_comment"] == "1" ? 1 : 0));
		$designer->set_prop("no_status", ($ci["no_status"] == "1" ? 1 : 0));
		$designer->save();

		$element_ords = array();

		$this->type_map = array(
			"text" => CL_PROPERTY_TEXTBOX,
			"textbox" => CL_PROPERTY_TEXTBOX,
			"relpicker" => CL_PROPERTY_SELECT,
			"callback" => CL_PROPERTY_TEXTBOX,
			"checkbox" => CL_PROPERTY_CHECKBOX,
			"fileupload" => CL_PROPERTY_TEXTBOX,
			"hidden" => CL_PROPERTY_TEXTBOX,
			"date" => CL_PROPERTY_TEXTBOX,
			"select" => CL_PROPERTY_SELECT,
			"date_select" => CL_PROPERTY_TEXTBOX,
			"password" => CL_PROPERTY_TEXTBOX,
			"submit" => CL_PROPERTY_TEXTBOX,
			"status" => CL_PROPERTY_TEXTBOX,
			"textarea" => CL_PROPERTY_TEXTAREA,
			"table" => CL_PROPERTY_TABLE,
			"chooser" => CL_PROPERTY_CHOOSER,
			"releditor" => CL_PROPERTY_TABLE,
			"datetime_select" => CL_PROPERTY_TEXTBOX,
			"aliasmgr" => CL_PROPERTY_TEXTBOX,
			"comments" => CL_PROPERTY_TEXTBOX,
			"toolbar" => CL_PROPERTY_TOOLBAR,
			"treeview" => CL_PROPERTY_TREE,
			"relmanager" => CL_PROPERTY_TEXTBOX,
			"calendar" => CL_PROPERTY_TEXTBOX,
			"objpicker" => CL_PROPERTY_TEXTBOX,
			"classificator" => CL_PROPERTY_CHOOSER,
			"popup_search" => CL_PROPERTY_TEXTBOX,
			"form" => CL_PROPERTY_TEXTBOX,
			"reminder" => CL_PROPERTY_TEXTBOX,
			"generated" => CL_PROPERTY_TEXTBOX,
			"colorpicker" => CL_PROPERTY_TEXTBOX,
			"time_select" => CL_PROPERTY_TEXTBOX,
		);

		// get groups
		$cnt = 0;
		foreach($gp as $gpid => $gpd)
		{
			// create group objs
			$g = $this->_get_object_by_parent_type_name($designer->id(), CL_PROPERTY_GROUP, $gpid);
			$g->set_prop("caption", $gpd["caption"]);
			$element_ords[$g->id()] = ++$cnt;
			if ($gpd["no_submit"])
			{
				$g->set_prop("no_submit", 1);
			}
			$g->save();

			// for each group make default grid
			$grid = $this->_get_object_by_parent_type_name($g->id(), CL_PROPERTY_GRID, "default");
			$element_ords[$grid->id()] = ++$cnt;

			// stick in properties
			foreach($ps as $pn => $pd)
			{
				if ($this->_prop_is_in_group($gpid , $pd["group"]))
				{
					$prop = $this->_get_object_by_parent_type_name($grid->id(), $this->type_map[$pd["type"]], $pn);
					$element_ords[$prop->id()] = ++$cnt;
					$prop_i = $prop->instance();
					if (method_exists($prop_i, "parse_property_from_class"))
					{
						$prop_i->parse_property_from_class($designer, $prop, $pd, $clid);
					}
				}
			}
		}

		$designer->set_meta("element_ords", $element_ords);
		$designer->save();

		// make relations
	}

	function _prop_is_in_group($gpid, $grp)
	{
		if (is_array($grp))
		{
			return in_array($gpid, $grp);
		}
		return $gpid == $grp;
	}

	function _get_object_by_parent_type_name($parent, $type, $name)
	{
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => $type,
			"name" => $name
		));
		if ($ol->count())
		{
			return $ol->begin();
		}
		$o = obj();
		$o->set_parent($parent);
		$o->set_class_id($type);
		$o->set_name($name);
		$o->save();
		return $o;
	}

	function _get_classes_tree($arr)
	{
		$clf = aw_ini_get("classfolders");
		foreach($clf as $id => $data)
		{
			$arr["prop"]["vcl_inst"]->add_item($data["parent"], array(
				"name" => $data["name"],
				"id" => $id,
				"url" => aw_url_change_var("clf", $id, aw_url_change_var("grp", null))
			));
		}
		if (!empty($arr["request"]["clf"]))
		{
			$arr["prop"]["vcl_inst"]->set_selected_item($arr["request"]["clf"]);
		}
		$arr["prop"]["vcl_inst"]->set_root_name(t("Klassid"));
		$arr["prop"]["vcl_inst"]->set_root_url(aw_url_change_var("clf", null));
	}

	function _get_classes_list($arr)
	{
		$clss = aw_ini_get("classes");
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_classes_list_table($t);
		$this->_filter_class_list($clss, $arr["request"]);

		$sum = array();
		foreach($clss as $class_id => $cld)
		{
			$o = obj();
			$o->set_class_id($class_id);
			$cld["id"] = $class_id;
			$cld["prop_cnt"] = 0;
			$cld["rel_cnt"] = 0;
			$cld["prop_table"] = 0;
			$cld["prop_meta"] = 0;
			foreach($o->get_property_list() as $pn => $pd)
			{
				$cld["prop_cnt"]++;
				if (empty($pd["store"]) || ($pd["store"] != "no" && $pd["store"] != "connect"))
				{
					if (!empty($pd["field"]) && $pd["field"] == "meta")
					{
						$cld["prop_meta"]++;
					}
					else
					{
						$cld["prop_table"]++;
					}
				
				}
			}
			foreach($o->get_relinfo() as $rid => $rdata)
			{
				if (is_numeric($rid))
				{
					$cld["rel_cnt"]++;
				}
			}
			
			foreach($cld as $k => $v)
			{
				if (!isset($sum[$k]))
				{
					$sum[$k] = 0;
				}
				if (is_numeric($v))
				{
					$sum[$k] += $v;
				}
			}
			$t->define_data($cld);
		}
		$t->set_default_sortby("name");
		$t->sort_by();
		$t->set_sortable(false);
		$t->define_data(array(
			"def" => html::strong(t("Summa")),
			"prop_cnt" => html::strong($sum["prop_cnt"]),
			"prop_table" => html::strong($sum["prop_table"]),
			"prop_meta" => html::strong($sum["prop_meta"]),
			"rel_cnt" => html::strong($sum["rel_cnt"]),
			"file" => count($clss)
		));
	}

	private function _filter_class_list(&$clss, $r)
	{
		if (empty($r["clf"]) && empty($r["grp"]))
		{
			return;
		}

		if ($this->can("view", $r["grp"]))
		{
			$o = obj($r["grp"]);
			$p = $o->class_list;
			foreach($clss as $clid => $cld)
			{
				if (!isset($p[$clid]))
				{
					unset($clss[$clid]);
				}
			}
			return;
		}
		// get all folders beneath $r["clf"] and then list all classes for those
		$clfs = array($r["clf"] => $r["clf"]);
		$c = aw_ini_get("classfolders");

		$this->_req_fetch_clfs($c, $r["clf"], $clfs);
		foreach($clss as $clid => $cld)
		{
			$pts = $this->make_keys(explode(",", isset($cld["parents"]) ? $cld["parents"] : ""));
			if (!count(array_intersect($clfs, $pts)))
			{
				unset($clss[$clid]);
			}
		}
	}

	private function _req_fetch_clfs($c, $parent, &$list)
	{
		foreach($c as $id => $dat)
		{
			if ($dat["parent"] == $parent)
			{
				$list[$id] = $id;
				$this->_req_fetch_clfs($c, $id, $list);
			}
		}
	}

	private function _init_classes_list_table(&$t)
	{
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id"
		));
		$t->define_field(array(
			"name" => "def",
			"caption" => t("ID"),
			"align" => "left",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Name"),
			"align" => "left",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "file",
			"caption" => t("Fail"),
			"align" => "left",
			"numeric" => 1,
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "prop_cnt",
			"caption" => t("Omadusi"),
			"align" => "right",
			"numeric" => 1,
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "rel_cnt",
			"caption" => t("Seoset&uuml;&uuml;pe"),
			"align" => "right",
			"numeric" => 1,
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "prop_table",
			"caption" => t("Omadusi tabelis"),
			"align" => "right",
			"numeric" => 1,
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "prop_meta",
			"caption" => t("Omadusi metadatas"),
			"align" => "right",
			"numeric" => 1,
			"sortable" => 1
		));
	}

	function _get_toolbar($arr)
	{
		$pt = isset($arr["request"]["grp"]) ? $arr["request"]["grp"] : $arr["obj_inst"]->id();
		$arr["prop"]["vcl_inst"]->add_button(array(
			"name" => "new",
			"img" => "new.gif",
			"onClick" => "len = document.changeform.elements.length;str  = '';
	for(i = 0; i < len; i++)
	{
		if (document.changeform.elements[i].name.indexOf('sel') != -1 && document.changeform.elements[i].checked)
		{
			str += '&sel['+document.changeform.elements[i].value+']='+document.changeform.elements[i].value;
		}
	}
	
window.location.href='".html::get_new_url(CL_SM_CLASS_STATS_GROUP, $pt, array("return_url" => get_ru()))."&'+str;",
			"url" => "#",
			"tooltip" => "new"
		));
	}

	function _get_classes_groups($arr)
	{	
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "smc",
				"persist_state" => true,
			),
			"root_item" => $arr["obj_inst"],
			"ot" => new object_tree(array(
				"class_id" => array(CL_SM_CLASS_STATS_GROUP),
				"parent" => $arr["obj_inst"]->id()
			)),
			"var" => "grp"
                ));
		foreach($arr["prop"]["vcl_inst"]->get_item_ids() as $id)
		{
			if ($id == $arr["obj_inst"]->id())
			{
				continue;
			}
			$d = $arr["prop"]["vcl_inst"]->get_item($id);
			$d["name"] .= " ".html::get_change_url($id, array("return_url" => get_ru()), html::img(array("url" => aw_ini_get("baseurl")."/automatweb//images/icons/edit.gif", "border" => "0")));
			$d["name"] .= " ".html::href(array(
				"url" => $this->mk_my_orb("delete", array("id" => $id, "return_url" => get_ru()), CL_SM_CLASS_STATS_GROUP), 
				"caption" => html::img(array("url" => aw_ini_get("baseurl")."/automatweb//images/icons/delete.gif", "border" => "0"))
			));
			$arr["prop"]["vcl_inst"]->set_item($d);
		}
	}

	function _get_cl_usage_stats_groups($arr)
	{	
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "smc",
				"persist_state" => true,
			),
			"root_item" => $arr["obj_inst"],
			"ot" => new object_tree(array(
				"class_id" => array(CL_SM_CLASS_STATS_GROUP),
				"parent" => $arr["obj_inst"]->id()
			)),
			"var" => "grp"
                ));
	}

	function _get_cl_usage_stats_tree($arr)
	{
		$clf = aw_ini_get("classfolders");
		foreach($clf as $id => $data)
		{
			$arr["prop"]["vcl_inst"]->add_item($data["parent"], array(
				"name" => $data["name"],
				"id" => $id,
				"url" => aw_url_change_var("clf", $id, aw_url_change_var("grp", null))
			));
		}
		if (!empty($arr["request"]["clf"]))
		{
			$arr["prop"]["vcl_inst"]->set_selected_item($arr["request"]["clf"]);
		}
		$arr["prop"]["vcl_inst"]->set_root_name(t("Klassid"));
		$arr["prop"]["vcl_inst"]->set_root_url(aw_url_change_var("clf", null));
	}

	function _get_cl_usage_stats_list($arr)
	{	
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_cl_usage_stats_list_table($t);

		$clss = aw_ini_get("classes");
		$this->_filter_class_list($clss, $arr["request"]);
		$clids = new aw_array(array_keys($clss));

		$clss = aw_ini_get("classes");
		$this->db_query("SELECT SUM(count) as cnt, class_id FROM aw_site_object_stats WHERE class_id IN (".$clids->to_sql().") GROUP BY class_id ORDER BY cnt desc");
		while($row = $this->db_next())
		{
			$t->define_data(array(
				"class" => $clss[$row["class_id"]]["name"],
				"total_cnt" => $row["cnt"]
			));
		}
		$t->set_default_sortby("total_cnt");
		$t->set_default_sorder("desc");
	}

	function _get_site_usage_stats_list($arr)
	{	
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_site_usage_stats_list_table($t);

		$clss = aw_ini_get("classes");
		$this->_filter_class_list($clss, $arr["request"]);
		$clids = new aw_array(array_keys($clss));

		$this->db_query("SELECT SUM(count) as cnt, site_id FROM aw_site_object_stats WHERE class_id IN (".$clids->to_sql().") GROUP BY site_id ORDER BY cnt desc");
		while($row = $this->db_next())
		{
			$t->define_data(array(
				"site_id" => get_instance("site_list")->get_url_for_site($row["site_id"]),
				"total_cnt" => $row["cnt"]
			));
		}
	}

	private function _init_cl_usage_stats_list_table($t)
	{
		$t->define_field(array(
			"name" => "class",
			"caption" => t("Klass"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "total_cnt",
			"caption" => t("Objekte kokku"),
			"align" => "right",
			"sortable" => 1,
			"numeric" => 1
		));
		$t->set_caption(t("Klasside kaupa"));
	}

	private function _init_site_usage_stats_list_table($t)
	{
		$t->define_field(array(
			"name" => "site_id",
			"caption" => t("Sait"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "total_cnt",
			"caption" => t("Objekte kokku"),
			"align" => "right",
			"sortable" => 1,
			"numeric" => 1
		));
		$t->set_caption(t("Saitide kaupa"));
	}

	function _get_cl_usage_props_stats_groups($arr)
	{	
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "smc",
				"persist_state" => true,
			),
			"root_item" => $arr["obj_inst"],
			"ot" => new object_tree(array(
				"class_id" => array(CL_SM_CLASS_STATS_GROUP),
				"parent" => $arr["obj_inst"]->id()
			)),
			"var" => "grp"
                ));
	}

	function _get_cl_usage_props_stats_tree($arr)
	{
		$clf = aw_ini_get("classfolders");
		foreach($clf as $id => $data)
		{
			$arr["prop"]["vcl_inst"]->add_item($data["parent"], array(
				"name" => $data["name"],
				"id" => $id,
				"url" => aw_url_change_var("clf", $id, aw_url_change_var("grp", null, aw_url_change_var("class_id", null)))
			));
			// all classes for that folder as well
			$clss = aw_ini_get("classes");
			foreach($clss as $clid => $cld)
			{
				$pts = $this->make_keys(explode(",", ifset($cld, "parents")));
				if (isset($pts[$id]))
				{
					$arr["prop"]["vcl_inst"]->add_item($id, array(
						"name" => $cld["name"],
						"id" => "cl_".$clid,
						"url" => aw_url_change_var("class_id", $clid, aw_url_change_var("clf", $id, aw_url_change_var("grp", null))),
						"iconurl" => icons::get_icon_url($clid)
					));
				}
			}
		}
		// add all classes that are addable but no parens
		$clss = aw_ini_get("classes");
		foreach($clss as $clid => $cld)
		{
			$ps = ifset($cld, "parents");
			if (ifset($cld, "can_add") == 1 && ($ps == "" || $ps == 0))
			{
				$arr["prop"]["vcl_inst"]->add_item(0, array(
					"name" => $cld["name"],
					"id" => "cl_".$clid,
					"url" => aw_url_change_var("class_id", $clid, aw_url_change_var("clf", $id, aw_url_change_var("grp", null))),
					"iconurl" => icons::get_icon_url($clid)
				));
			}
		}

		if (!empty($arr["request"]["class_id"]))
		{
			$arr["prop"]["vcl_inst"]->set_selected_item("cl_".$arr["request"]["class_id"]);
		}
		$arr["prop"]["vcl_inst"]->set_root_name(t("Klassid"));
		$arr["prop"]["vcl_inst"]->set_root_url(aw_url_change_var("clf", null));
	}

	function _get_cl_usage_props_stats_list($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_cl_usage_props_stats_list($t);

		if (empty($arr["request"]["class_id"]))
		{
			return;
		}

		$total_site_count = $this->db_fetch_field("SELECT count(distinct(site_id)) as cnt from aw_site_class_prop_stats", "cnt");
		$class_id = $arr["request"]["class_id"];
		$this->db_query("SELECT prop, count(site_id) as num_sites, sum(set_objs) as num_objects, sum(total_objs) as total_objs  FROM aw_site_class_prop_stats WHERE class_id = $class_id GROUP BY prop ");
		while ($row = $this->db_next())
		{
			$t->define_data(array(
				"prop" => $row["prop"],
				"num_sites" => $row["num_sites"],
				"num_objects" => $row["num_objects"],
				"total_objs" => $row["total_objs"],
				"usage_pct_sites" => number_format((100.0 * $row["num_sites"]) / $total_site_count, 2),
				"usage_pct_objs" => number_format( (100.0 * $row["num_objects"]) / $row["total_objs"], 2)
			));
		}
		
	}

	private function _init_cl_usage_props_stats_list($t)
	{
		$t->define_field(array(
			"name" => "prop",
			"caption" => t("Omadus"),
			"align" => "left",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "num_sites",
			"caption" => t("Kasutusel saitides"),
			"align" => "left",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "total_objs",
			"caption" => t("Kokku objekte"),
			"align" => "left",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "num_objects",
			"caption" => t("Kasutusel objektides"),
			"align" => "left",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "usage_pct_sites",
			"caption" => t("Kasutuse % saitide kaupa"),
			"align" => "left",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "usage_pct_objs",
			"caption" => t("Kasutuse % objektide kaupa"),
			"align" => "left",
			"sortable" => 1
		));
	}

	function _get_cl_usage_stats_tms_tree($arr)
	{
		// tree by server / site
		$server_list = $this->do_orb_method_call(array(
			"class" => "site_list",
			"action" => "orb_get_server_list",
			"method" => "xmlrpc",
			"server" => "register.automatweb.com"
		));

		$tv = $arr["prop"]["vcl_inst"];

		$used_servers = array();
		$site_list = get_instance("install/site_list")->get_local_list();
		foreach($site_list as $site)
		{
			if ($site["site_used"] == 1)
			{
				if ($site["server_id"] == 0)
				{
					$site["server_id"] = -1;
				}
				$tv->add_item($site["server_id"], array(
					"id" => "site_".$site["id"],
					"name" => ifset($arr, "request", "site") == $site["id"] ? html::strong($site["url"]) : $site["url"],
					"url" => aw_url_change_var("site", $site["id"])
				));
				$used_servers[$site["server_id"]] = $site["server_id"];
			}
		}

		foreach($used_servers as $server_id)
		{
			if ($server_id == -1)
			{
				$tv->add_item(0, array(
					"id" => $server_id,
					"name" => ifset($arr, "request", "server") == $server_id ? html::strong(t("Tundmatu")) : t("Tundmatu"),
					"url" => aw_url_change_var("server", $server_id)
				));
				continue;
			}

			foreach($server_list as $server)
			{
				if ($server["id"] == $server_id)
				{
					$tv->add_item(0, array(
						"id" => $server_id,
						"name" => ifset($arr, "request", "server") == $server_id ? html::strong($server["name"]) : $server["name"],
						"url" => aw_url_change_var("server", $server_id)
					));
				}
			}
		}
	}

	private function _init_t2($t)
	{	
		$t->define_field(array(
			"name" => "server",
			"caption" => t("Server"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "site",
			"caption" => t("Sait"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "pageviews",
			"caption" => t("Lehevaatamisi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "avg_time",
			"caption" => t("Keskmine lehe aeg"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "max_time",
			"caption" => t("Pikim lehe aeg"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "min_time",
			"caption" => t("V&auml;ikseim lehe aeg"),
			"align" => "center",
			"sortable" => 1
		));
	}

	function _get_cl_usage_stats_tms_list($arr)
	{	
		$t = $arr["prop"]["vcl_inst"];
		$this->_init_t2($t);

		$wh = array();
		if (!empty($arr["request"]["server"]))
		{
			$this->quote(&$arr["request"]["server"]);
			$wh[] = " server_id = ".$arr["request"]["server"];
		}
		if (!empty($arr["request"]["site"]))
		{
			$this->quote(&$arr["request"]["site"]);
			$wh[] = " site_id = ".$arr["request"]["site"];
		}

		$whs = join(" AND ", $wh);
		if ($whs != "")
		{
			$whs = " WHERE ".$whs;
		}
		$this->db_query("SELECT server_id as server, site_id as site, count(*) as pageviews, AVG(exec_time) as avg_time, max(exec_time) as max_time, min(exec_time) as min_time FROM aw_timing_stats $whs GROUP BY server_id, site_id");
		while ($row = $this->db_next())
		{
			$row["site"] = get_instance("install/site_list")->get_url_for_site($row["site"]);
			$t->define_data($row);
		}
		$t->set_default_sortby("pageviews");
		$t->set_default_sorder("desc");
	}

	/**
		@attrib name=import_logs nologin="1"
	**/
	function import_logs($arr)
	{
		$base = aw_ini_get("site_basedir")."/files/timers/";
		foreach(glob($base."tm-*") as $file)
		{
			echo "process $file <br>\n";
			flush();
			foreach(file($file) as $line)
			{
				if (preg_match("/(.*) request (.*)/", $line, $mt))
				{	
					list($d, $tm) = explode(" ", $mt[1]);
					list($d, $m, $y) = explode(".", $d);
					list($h, $i, $s) = explode(":", $tm);

					$q = "INSERT INTO aw_timing_stats(server_id, site_id, exec_time, tm) values(1, ".aw_ini_get("site_id").", '".$mt[2]."', ".mktime($h, $i, $s, $m, $d, $y).")";
					$this->db_query($q);
				}
			}
			unlink($file);
		}	
		die("all done");
	}
}
?>
