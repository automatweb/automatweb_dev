<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/class_designer_manager.aw,v 1.3 2005/05/17 09:14:01 kristo Exp $
// class_designer_manager.aw - Klasside brauser 
/*

@classinfo syslog_type=ST_CLASS_DESIGNER_MANAGER relationmgr=yes no_comment=1 no_status=1

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

@groupinfo mgr caption="Manager" submit=no
@groupinfo rels caption="Seosed" submit=no
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
		$arr["tf"] = $_REQUEST["tf"];
		$arr["classf_name"] = "";
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
			"name" => "add",
			"caption" => t("Lisa"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "design",
			"caption" => t("Disaini"),
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
				"sel" => $sel
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

			$add_link = html::href(array(
				"url" => $this->mk_my_orb(
					"check_add_object",
					array(
						"clid" => $clid,
						"id" => $arr["obj_inst"]->id(),
						"ru" => get_ru()
					)
				),
				"caption" => t("Demo objekt"),
			));

			$sel = html::checkbox(array(
				"name" => "sel[]",
				"value" => $clid
			));

			$t->define_data(array(
				"name" => $cld["name"],
				"add" => $add_link,
				"design" => $design,
				"clid_nm" => $cld["def"],
				"size" => $this->get_class_size($cld["file"]),
				"icon" => html::img(array(
					"url" => aw_ini_get("icons.server")."/class_".$clid.".gif"
				)),
				"sel" => $sel
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
		$pt = $this->_get_class_path_in_tree($arr["clid"]);
		$o = obj($arr["id"]);
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

		$ol = new object_list(array(
			"parent" => $o->id(),
			"class_id" => $arr["clid"],
		));

		if (!$ol->count())
		{
			return html::get_new_url($arr["clid"], $o->id(), array("return_url" => urlencode($arr["ru"])));
		}
		else
		{
			$o = $ol->begin();
			return html::get_change_url($o->id(), array("return_url" => urlencode($arr["ru"])));
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

	**/
	function cut_p($arr)
	{
		$_SESSION["class_designer"]["cut_classes"] = safe_array($arr["sel"]);
		$_SESSION["class_designer"]["cut_folders"] = safe_array($arr["sel_fld"]);
		return $arr["post_ru"];
	}

	/**

		@attrib name=copy_p

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
			$this->_set_ini_file_value(aw_ini_get("basedir")."/config/ini/classes.ini", "classes[$clid][parents]", $arr["tf"]);
			$this->_set_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classes[$clid][parents]", $arr["tf"]);
		}
		$_SESSION["class_designer"]["cut_classes"] = array();

		$cut = safe_array($_SESSION["class_designer"]["cut_folders"]);
		foreach($cut as $fld)
		{
			$this->_set_ini_file_value(aw_ini_get("basedir")."/config/ini/classfolders.ini", "classfolders[$fld][parent]", $arr["tf"]);
			$this->_set_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classfolders[$fld][parent]", $arr["tf"]);
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
			$this->_set_ini_file_value(aw_ini_get("basedir")."/config/ini/classes.ini", "classes[$clid][parents]", $np);
			$this->_set_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classes[$clid][parents]", $np);
		}
		$_SESSION["class_designer"]["copied_classes"] = array();

		$flds = aw_ini_get("classfolders");
		$cop = safe_array($_SESSION["class_designer"]["copied_folders"]);
		foreach($cop as $clid)
		{
			$max_fld = max(array_keys($flds))+1;
			$np = $arr["tf"];
			$ds = $flds[$clid];

			$this->_add_ini_file_value(aw_ini_get("basedir")."/config/ini/classfolders.ini", "classfolders[$max_fld][name]", $ds["name"]);
			$this->_add_ini_file_value(aw_ini_get("basedir")."/config/ini/classfolders.ini", "classfolders[$max_fld][parent]", $np);

			$this->_add_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classfolders[$max_fld][name]", $ds["name"]);
			$this->_add_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classfolders[$max_fld][parent]", $np);
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

		$this->_add_ini_file_value(aw_ini_get("basedir")."/config/ini/classfolders.ini", "classfolders[$max_fld][name]", $arr["classf_name"]);
		$this->_add_ini_file_value(aw_ini_get("basedir")."/config/ini/classfolders.ini", "classfolders[$max_fld][parent]", $arr["tf"]);

		$this->_add_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classfolders[$max_fld][name]", $arr["classf_name"]);
		$this->_add_ini_file_value(aw_ini_get("basedir")."/aw.ini", "classfolders[$max_fld][parent]", $arr["tf"]);
		
		return $arr["post_ru"];
	}
}
?>
