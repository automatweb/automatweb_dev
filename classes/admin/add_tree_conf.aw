<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/add_tree_conf.aw,v 1.26 2005/03/01 13:21:56 duke Exp $
// add_tree_conf.aw - Lisamise puu konff

/*

	@classinfo no_comment=1 no_status=1

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
					if ($cld["alias"] != "")
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
						if ($cld["alias"] != "")
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
					"type" => 5 /* RELTYPE_ADD_TREE from core/users/group */
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

		$clss = aw_ini_get("classes");
		$grps = aw_ini_get("classfolders");

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

				if ($show /*&& $clid != CL_MENU*/)
				{
					$ret[$clid] = $clid;
				}
			}
		}

		//$ret[CL_MENU] = CL_MENU;
		return $ret;
	}

	/** returns true if the given class can be used in the given conf
	
		@comment
			
			$atc - add_tree_conf object instance
			$class - the name of the class to check access to

	**/
	function can_access_class($atc, $class)
	{
		$grps = aw_ini_get("classfolders");
		$us = $atc->meta("usable");
		
		$class_id = false;
		
		$clss = aw_ini_get("classes");
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
		/*if (aw_global_get("uid") == "kix")
		{
			echo "ret = ".dbg::dump($us);
		}*/
		if ($class_id != CL_MENU && $ret)
		{
			$v = $atc->meta("visible");
			// also, if the class is in some groups and for all those groups access has been turned off
			// do not show the alias
			$grp = explode(",",$clss[$class_id]["parents"]);
			$show = false;
			/*if (aw_global_get("uid") == "kix")
			{
				echo dbg::dump($grp);
			}*/
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
						/*if (aw_global_get("uid") == "kix")
						{
							echo "set no has grp from $g <br>";
						}*/
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
}
?>
