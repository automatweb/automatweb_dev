<?php
/* 

@classinfo syslog_type=ST_ACL relationmgr=yes

@groupinfo general caption=Üldine
@default group=general
@default table=objects

@property chain field=meta method=serialize type=relpicker reltype=RELATION_CHAIN
@caption Vali p&auml;rg

@property role field=meta method=serialize type=relpicker reltype=RELATION_ROLE
@caption Vali roll

@property groups field=meta method=serialize type=relpicker multiple=1 reltype=RELATION_GROUP
@caption Vali grupid

@property priority field=meta method=serialize type=textbox
@caption Prioriteet

@property update_acl field=meta method=serialize type=checkbox ch_value=1 store=no 
@caption Uuenda ACL

*/

define("RELATION_CHAIN", 1);
define("RELATION_ROLE", 2);
define("RELATION_GROUP", 3);

class acl_class extends class_base
{
	function acl_class()
	{
		$this->init(array(
			"tpldir" => "automatweb/acl",
			"clid" => CL_ACL
		));
	}

	function update_acl($id)
	{
		$o = $this->get_object($id);
		$meta = $this->get_object_metadata(array(
			"metadata" => $o["metadata"]
		));

		$oc = get_instance("object_chain");
		$objs = new aw_array($oc->get_objects_in_chain($meta["chain"]));

		$ro = get_instance("role");
		$mask = $ro->get_acl_mask($meta["role"]);
		$aclarr = $ro->get_acl_values($meta["role"]);

		$u = get_instance("users");
		$_gads = new aw_array($meta["added_groups"]);
		$gads = array();
		foreach($_gads->get() as $g_oid)
		{
			$g_gid = $u->get_gid_for_oid($g_oid);
			$gads[$g_gid] = $g_gid;
		}

		foreach($objs->get() as $oid)
		{
			$o_grps = $this->get_acl_groups_for_obj($oid);
			$mg = new aw_array($meta["groups"]);
			foreach($mg->get() as $grp)
			{
				$grp = $u->get_gid_for_oid($grp);

				// ok, before we do this, we must check if another acl object, that
				// has a higher priority, includes this folder<->group relation
				// and if one is found, then we must not set the acl.
				if (!$this->higher_priority_acl_exists($id,$meta["priority"], $oid, $grp))
				{
					if (!isset($o_grps[$grp]))
					{
						$this->add_acl_group_to_obj($grp,$oid);
						$gads[$grp][$oid] = $grp;
					}

					$this->save_acl_masked($oid,$grp,$aclarr,$mask);
				}
			}
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "added_groups",
			"value" => $gads
		));
	}

	function get_acls_for_role($role)
	{
		$objs = $this->list_objects(array(
			"class" => CL_ACL,
			"return" => ARR_ALL
		));

		$ret = array();
		foreach($objs as $oid => $odata)
		{
			$meta = $this->get_object_metadata(array(
				"metadata" => $odata["metadata"]
			));
			if ($meta["role"] == $role)
			{
				$ret[$oid] = $oid;
			}
		}
		return $ret;
	}

	function higher_priority_acl_exists($cur_acl, $cur_priority, $oid, $grp)
	{
		if (!isset($this->acl_object_cache))
		{
			$this->acl_object_cache = array();
			$ol = $this->list_objects(array(
				"class" => CL_ACL,
				"return" => ARR_ALL
			));
			foreach($ol as $obj)
			{
				if ($obj['oid'] != $cur_acl)
				{
					$obj['meta'] = $this->get_object_metadata(array(
						'metadata' => $obj['metadata']
					));
					$this->acl_object_cache[$obj['oid']] = new acl_class;
					$this->acl_object_cache[$obj['oid']]->acl_obj = $obj;
				}
			}
		}

		foreach($this->acl_object_cache as $_oid => $obj)
		{
			if ($obj->relation_exists($oid, $grp) && $obj->get_priority() > $cur_priority)
			{
				return true;
			}
		}
		return false;
	}

	function get_priority()
	{
		return $this->acl_obj['meta']['priority'];
	}

	function relation_exists($oid, $grp)
	{
		$meta = $this->acl_obj['meta'];

		$oc = get_instance("object_chain");
		$objs = new aw_array($oc->get_objects_in_chain($meta["chain"]));

		foreach($objs->get() as $_oid)
		{	
			$gpa = new aw_array($meta["groups"]);
			foreach($gpa->get() as $_grp)
			{
				if ($oid == $_oid && $grp == $_grp)
				{
					return true;
				}
			}
		}
		return false;
	}

	function get_acls_for_group($gid)
	{
		$objs = $this->list_objects(array(
			"class" => CL_ACL,
			"return" => ARR_ALL
		));

		$ret = array();
		foreach($objs as $oid => $odata)
		{
			$meta = $this->get_object_metadata(array(
				"metadata" => $odata["metadata"]
			));
			if ($meta["groups"][$gid] == $gid)
			{
				$ret[$oid] = $oid;
			}
		}
		return $ret;
	}

	function get_roles_for_acl($oid)
	{
		$ob = $this->get_object($oid);
		return $ob['meta']['role'];
	}

	function callback_get_rel_types()
	{
		return array(
			RELATION_CHAIN => "p&auml;rg",
			RELATION_ROLE => "roll",
			RELATION_GROUP => "grupp"
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELATION_CHAIN)
		{
			return array(CL_OBJECT_CHAIN);
		}
		if ($args["reltype"] == RELATION_ROLE)
		{
			return array(CL_ROLE);
		}
		if ($args["reltype"] == RELATION_GROUP)
		{
			return array(CL_GROUP);
		}
	}

	////
	// !removes group $gid from acl object $acl
	function remove_group_from_acl($acl, $gid)
	{
		$ob = $this->get_object($acl);
		unset($ob['meta']['groups'][$gid]);
		$this->set_object_metadata(array(
			"oid" => $acl,
			"key" => "groups",
			"value" => $ob['meta']['groups']
		));
	}

	function set_property($arr)
	{
		$prop =& $arr["prop"];
		$meta =& $arr["metadata"];

		switch($prop["name"])
		{
			case "groups":
				$ags = array();
				if (is_array($meta["added_groups"]))
				{
					foreach($meta["added_groups"] as $gid => $gdata)
					{
						if ($prop["value"][$gid] != $gid)
						{
							// this group was removed, so remove all acl rels for that group
							foreach($gdata as $oid => $_gid)
							{
								$this->remove_acl_group_from_obj($gid,$oid);
							}
						}
						else
						{
							$ags[$gid] = $gdata;
						}
					}
				}
				$meta["added_groups"] = $ags;
				break;

			case "update_acl":
				if ($arr["form_data"]["update_acl"] == 1 && $arr["obj"]["oid"])
				{
					$this->_do_update_acl = 1;
				}
				break;
		}
		return PROP_OK;
	}
	
	function callback_post_save($arr)
	{
		if ($this->_do_update_acl)
		{
			$this->update_acl($arr["id"]);
		}
	}
}
?>
