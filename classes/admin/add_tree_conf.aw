<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/add_tree_conf.aw,v 1.9 2004/03/09 14:17:28 kristo Exp $
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
			"caption" => "Nimi"
		));

		$t->define_field(array(
			"name" => "visible",
			"caption" => "N&auml;htav",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "usable",
			"caption" => "Kasutatav",
			"align" => "center"
		));


		$t->define_field(array(
			"name" => "alias_add",
			"caption" => "Aliasena lisatav",
			"align" => "center"
		));

		if (!is_array($visible) || !is_array($usable))
		{
			$visible = array();
			$usable = array();
			$alias_add = array();
			foreach($this->cfg["classfolders"] as $id => $d)
			{
				$visible["fld"][$id] = 1;
			}
			foreach($this->cfg["classes"] as $id => $d)
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
			foreach($this->cfg["classes"] as $cl_id => $cld)
			{
				if (!$cld["can_add"] || !isset($cld["parents"]))
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
		foreach($this->cfg["classfolders"] as $id => $cfd)
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

				foreach($this->cfg["classes"] as $cl_id => $cld)
				{
					if ($cld["parents"] == "" || !$cld["can_add"])
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

	////
	// !gets the root menu for the current user from the conf object with id $id
	function get_root_for_user($id)
	{
		$ob = new object($id);
		$gidlist = aw_global_get("gidlist");

		$root_id = 0;
	
		$max_pri = 0;
		$max_gid = 0;
		$pri_inst = get_instance("priority");
		$grps = $pri_inst->get_groups($ob->prop("priority_id"));
		foreach($gidlist as $ugid)
		{
			if ($grps[$ugid])
			{
				if ($max_pri < $grps[$ugid])
				{
					$max_pri = $grps[$ugid];
					$max_gid = $ugid;
				}
			}
		}
		// now we have the gid with max priority
		if ($max_gid)
		{
			$grps = $ob->meta("grps");
			// find the root menu for this gid
			$root_oid = $grps[$max_gid];
			if ($root_oid)
			{
				$tr_obj = new object($root_oid);
				$root_id = $tr_obj->prop("root");
			}
		}
		return $root_id;
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
		$ret = array();

		foreach($r as $clid => $one)
		{
			if ($one == 1)
			{
				$ret[$clid] = $clid;
			}
		}

		return $ret;
	}
}
?>
