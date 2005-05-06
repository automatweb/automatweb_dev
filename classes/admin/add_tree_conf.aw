<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/add_tree_conf.aw,v 1.31 2005/05/06 09:20:09 kristo Exp $
// add_tree_conf.aw - Lisamise puu konff

/*

	@classinfo no_comment=1 no_status=1 syslog_type=ST_ADD_TREE_CONF

	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property sel type=table store=no group=general no_caption=1

*/

class add_tree_conf extends class_base
{
	function add_tree_conf()
	{
		$this->init(array(
			"clid" => CL_ADD_TREE_CONF,
		));
	}

	function get_property($arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
			case "sel":
				$this->_do_sel_tbl($prop, $arr["obj_inst"]->meta("visible"), $arr["obj_inst"]->meta("usable"), $arr["obj_inst"]->meta("alias_add"));
				break;
		}

		return PROP_OK;
	}

	function set_property($arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
			case "sel":
				$arr["obj_inst"]->set_meta("visible", is_array($arr["request"]["visible"]) ? $arr["request"]["visible"] : array());
				$arr["obj_inst"]->set_meta("usable", is_array($arr["request"]["usable"]) ? $arr["request"]["usable"] : array());
				$arr["obj_inst"]->set_meta("alias_add", is_array($arr["request"]["alias_add"]) ? $arr["request"]["alias_add"] : array());
				break;
		}

		return PROP_OK;
	}

	function callback_pre_save($arr)
	{
		// save folder structure from ini file
		$arr["obj_inst"]->set_meta("folder_structure", aw_ini_get("classfolders"));
		$arr["obj_inst"]->set_meta("class_structure", aw_ini_get("classes"));
	}

	function _do_sel_tbl(&$arr, $visible, $usable, $alias_add)
	{
		$t =& $arr["vcl_inst"];

		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi")
		));

		$t->define_field(array(
			"name" => "visible",
			"caption" => "<a href='javascript:void(0)' onClick='aw_sel_chb(document.changeform,\"visible\")'>".t("N&auml;htav")."</a>",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "usable",
			"caption" => "<a href='javascript:void(0)' onClick='aw_sel_chb(document.changeform,\"usable\")'>".t("Kasutatav")."</a>",
			"align" => "center"
		));


		$t->define_field(array(
			"name" => "alias_add",
			"caption" => "<a href='javascript:void(0)' onClick='aw_sel_chb(document.changeform,\"alias_add\")'>".t("Aliasena lisatav")."</a>",
			"align" => "center"
		));

		if (!is_array($visible) || !is_array($usable))
		{
			$visible = array();
			$usable = array();
			$alias_add = array();
			
			$clsf = aw_ini_get("classfolders");
			foreach($clsf as $id => $d)
			{
				$visible["fld"][$id] = 1;
			}
			$tmp = aw_ini_get("classes");
			foreach($tmp as $id => $d)
			{
				$visible["obj"][$id] = 1;
				$usable[$id] = 1;
				if ($d["alias"] != "")
				{
					$alias_add[$id] = 1;
				}
			}
		}

		$t->set_sortable(false);

		$this->level = -1;
		$this->_req_do_table($t, 0, $visible, $usable, $alias_add);
	}

	function _req_do_table(&$t, $parent, $visible, $usable, $alias_add)
	{
		if ($this->level == -1)
		{
			$tmp = aw_ini_get("classes");
			foreach($tmp as $cl_id => $cld)
			{
				if (!isset($cld["parents"]))
				{
					continue;
				}

				if ($cld["parents"] == 0)
				{
					$ala = "";
					if (true || $cld["alias"] != "")
					{
						$ala = html::checkbox(array(
							"name" => "alias_add[$cl_id]",
							"value" => 1,
							"checked" => $alias_add[$cl_id] == 1
						));
					}

					$t->define_data(array(
						"name" => str_repeat("&nbsp;", ($this->level+1) * 10).$cld["name"],
						"visible" => html::checkbox(array(
							"name" => "visible[obj][$cl_id]",
							"value" => 1,
							"checked" => $visible["obj"][$cl_id] == 1
						)),
						"usable" => html::checkbox(array(
							"name" => "usable[$cl_id]",
							"value" => 1,
							"checked" => $usable[$cl_id] == 1
						)),
						"alias_add" => $ala,
					));
				}
			}
		}

		$this->level++;
		$clsf = aw_ini_get("classfolders");
		foreach($clsf as $id => $cfd)
		{
			if ($cfd["parent"] == $parent)
			{
				$t->define_data(array(
					"name" => str_repeat("&nbsp;", $this->level * 10)."<b>".$cfd["name"]."</b>",
					"visible" => html::checkbox(array(
						"name" => "visible[fld][$id]",
						"value" => 1,
						"checked" => $visible["fld"][$id] == 1
					)),
					
				));

				$tmp = aw_ini_get("classes");
				foreach($tmp as $cl_id => $cld)
				{
					if ($cld["parents"] == "")
					{
						continue;
					}

					$pss = $this->make_keys(explode(",", $cld["parents"]));
					if ($pss[$id])
					{
						$ala = "";
						if (true || $cld["alias"] != "")
						{
							$ala = html::checkbox(array(
								"name" => "alias_add[$cl_id]",
								"value" => 1,
								"checked" => $alias_add[$cl_id] == 1
							));
						}

						$t->define_data(array(
							"name" => str_repeat("&nbsp;", ($this->level+1) * 10).$cld["name"],
							"visible" => html::checkbox(array(
								"name" => "visible[obj][$cl_id]",
								"value" => 1,
								"checked" => $visible["obj"][$cl_id] == 1
							)),
							"usable" => html::checkbox(array(
								"name" => "usable[$cl_id]",
								"value" => 1,
								"checked" => $usable[$cl_id] == 1
							)),
							"alias_add" => $ala,
						));
					}
				}

				$this->_req_do_table($t, $id, $visible, $usable, $alias_add);
			}
		}
		$this->level--;
	}

	/** returns the active add_tree_conf for the current user, false if none
	**/
	function get_current_conf()
	{
		if (aw_ini_get("acl.check_prog") == 0)
		{
			return false;
		}
		$ret = false;

		// go over groups and for each check if it has the conf
		$cur_max = 0;
		$gidlist_oid = aw_global_get("gidlist_oid");
		if (is_array($gidlist_oid))
		{
			foreach($gidlist_oid as $g_oid)
			{
				if (!is_oid($g_oid))
				{
					continue;
				};
				if (!$this->can("view", $g_oid))
				{
					continue;
				}
				$o = obj($g_oid);
				$c = $o->connections_from(array(
					"type" => "RELTYPE_ADD_TREE" /* from core/users/group */
				));
				if (count($c) > 0 && $o->prop("priority") > $cur_max)
				{
					$cur_max = $o->prop("priority");
					$fc = reset($c);
					$ret = $fc->prop("to");
				}
			}
		};

		if (!$ret)
		{
			$ret = aw_ini_get("add_tree_conf.default");
		}

		if (!$this->can("view", $ret))
		{
			$ret = 0;
		}

		return $ret;
	}

	/** returns the list of alias-addable classes for tree conf $id
	**/
	function get_alias_filter($id)
	{
		$o = obj($id);
		$r = $o->meta("alias_add");
		$v = $o->meta("visible");
		$ret = array();

		$clss = $o->meta("class_structure");
		if (!is_array($clss))
		{
			$clss = aw_ini_get("classes");
		}

		$grps = $o->meta("folder_structure");
		if (!is_array($grps))
		{
			$grps = aw_ini_get("classfolders");
		}

		foreach($r as $clid => $one)
		{
			if ($one == 1)
			{
				// also, if the class is in some groups and for all those groups access has been turned off
				// do not show the alias
				$grp = explode(",",$clss[$clid]["parents"]);
				$show = false;
				foreach($grp as $g)
				{
					// must check group parents as well :(
					// but CL_MENU has no parent (g == 0) and we have to deal with it -- duke
					$has_grp = $v["fld"][$g] || $g == 0;
					if ($has_grp && $g != 0)
					{
						while ($g)
						{
							if (!$v["fld"][$g])
							{
								$has_grp = false;
								break;
							}
							$g = $grps[$g]["parent"];
						}
					}

					if ($has_grp)
					{
						$show = true;
					}
				}
				if (aw_ini_get("site_id") == 214)
				{
					$show = true;
				}

				if ($show)
				{
					$ret[$clid] = $clid;
				}
			}
		}
		return $ret;
	}

	/** returns true if the given class can be used in the given conf
	
		@comment
			
			$atc - add_tree_conf object instance
			$class - the name of the class to check access to

	**/
	function can_access_class($atc, $class)
	{
		$grps = $atc->meta("folder_structure");
		if (!is_array($grps))
		{
			$grps = aw_ini_get("classfolders");
		}
		$us = $atc->meta("usable");
		
		$class_id = false;
		
		$clss = $atc->meta("class_structure");
		if (!is_array($clss))
		{
			$clss = aw_ini_get("classes");
		}

		if (is_class_id($class))
		{
			$class_id = $class;
		}
		else
		{
			foreach($clss as $clid => $cld)
			{
				if (basename($cld["file"]) == $class)
				{
					$class_id = $clid;
					break;
				}
			}
		}
		if (!$class_id)
		{
			return true;
		}

		$ret = $us[$class_id] == 1;
		if ($class_id != CL_MENU && $ret)
		{
			$v = $atc->meta("visible");
			// also, if the class is in some groups and for all those groups access has been turned off
			// do not show the alias
			$grp = explode(",",$clss[$class_id]["parents"]);
			$show = false;
			foreach($grp as $g)
			{
				// must check group parents as well :(
				$has_grp = $v["fld"][$g];
				if ($has_grp)
				{
					while ($g)
					{
						if (!$v["fld"][$g])
						{
							$has_grp = false;
							break;
						}
						$g = $grps[$g]["parent"];
					}
				}

				if ($has_grp)
				{
					$show = true;
				}
			}

			if (!$show)
			{
				$ret = false;
			}
		}

		return $ret;
	}

	function on_site_init($dbi, $site, &$ini_opts, &$log, &$osi_vars)
	{
		$o = obj($osi_vars["add_tree_conf"]);
		$this->adc_set_all($o);

		$clss = aw_ini_get("classes");
	
		//  Dokumendi seostehalduris kuvatakse vaikimisi järgmisi objekte:
		$alias_addable = array(CL_EXTLINK, CL_FILE, CL_IMAGE, CL_LAYOUT, CL_WEBFORM, CL_MINI_GALLERY, CL_DOCUMENT, CL_MENU_TREE, CL_ML_LIST, CL_PROMO);

		foreach($clss as $clid => $cld)
		{
			$this->adc_set_class($o, $clid, true, true, in_array($clid, $alias_addable));
		}
		
		// Kohe tuleb välja jätta järgmiste programmide kasutamise võimalus:

		// Sisuhaldus > Taket
		$this->adc_set_fld($o, 44, false);

		// Otsingud > Saidi otsing (vist mingi vana objekt)
		$this->adc_set_class($o, CL_SITE_SEARCH, false, false, false);

		// Varia & Vanad > Dokument(p), Foorum (vana), Stamp, Mailinglisti seaded
		$this->adc_set_class($o, CL_PERIODIC_SECTION, false, false, false);
		$this->adc_set_class($o, CL_FORUM, false, false, false);
		$this->adc_set_class($o, CL_ML_STAMP, false, false, false);
		$this->adc_set_class($o, CL_ML_LIST_CONF, false, false, false);
		
		// Süsteemi haldus > Töösolevad klassid
		$this->adc_set_fld($o, 19, false);

		// Süsteemi haldus > Varia & Vanad
		$this->adc_set_fld($o, 4, false);

		$o->save();

		echo "saved add tree conf! <br>\n";
		flush();

		aw_disable_messages();
		// seostada Administraatorid ja Toimetajad grupiga
		$adm_g = obj($osi_vars["groups.admins"]);
		$adm_g->connect(array(
			"to" => $o->id(),
			"reltype" => 5 // RELTYPE_ADD_TREE
		));

		$ed_g = obj($osi_vars["groups.editors"]);
		$ed_g->connect(array(
			"to" => $o->id(),
			"reltype" => 5 // RELTYPE_ADD_TREE
		));
		aw_restore_messages();
	}

	function adc_set_all($o)
	{
		$visible = array();
		$usable = array();
		$alias_add = array();
			
		$clsf = aw_ini_get("classfolders");
		foreach($clsf as $id => $d)
		{
			$visible["fld"][$id] = 1;
		}
		$tmp = aw_ini_get("classes");
		foreach($tmp as $id => $d)
		{
			$visible["obj"][$id] = 1;
			$usable[$id] = 1;
			if ($d["alias"] != "")
			{
				$alias_add[$id] = 1;
			}
		}

		$o->set_meta("visible", $visible);
		$o->set_meta("usable", $usable);
		$o->set_meta("alias_add", $alias_add);
	}

	function adc_set_class($o, $clid, $visible, $usable, $alias_add)
	{
		$v = $o->meta("visible");
		$u = $o->meta("usable");
		$a = $o->meta("alias_add");

		if (!$visible)
		{
			unset($v["obj"][$clid]);
		}
		else
		{
			$v["obj"][$clid] = $visible;
		}
		if (!$usable)
		{
			unset($u[$clid]);
		}
		else
		{
			$u[$clid] = $usable;
		}
		if (!$alias_add)
		{
			unset($a[$clid]);
		}
		else
		{
			$a[$clid] = $alias_add;
		}

		$o->set_meta("visible", $v);
		$o->set_meta("usable", $u);
		$o->set_meta("alias_add", $a);
	}

	function adc_set_fld($o, $fld, $visible)
	{
		$v = $o->meta("visible");
		if (!$visible)
		{
			unset($v["fld"][$fld]);
		}
		else
		{
			$v["fld"][$fld] = $visible;
		}
		$o->set_meta("visible", $v);
	}
}
?>
