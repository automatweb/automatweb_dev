<?php

class acl_class extends aw_template
{
	function acl_class()
	{
		$this->init("automatweb/acl");
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$this->mk_path($parent,"Lisa ACL");

		$roles = $this->list_objects(array(
			"class" => CL_ROLE
		));

		$chains = $this->list_objects(array(
			"class" => CL_OBJECT_CHAIN
		));

		$u = get_instance("users");
		$groups = $u->get_group_picker(array(
			"type" => array(GRP_REGULAR,GRP_DEFAULT,GRP_DYNAMIC,GRP_USERGRP)
		));

		$this->vars(array(
			"roles" => $this->picker(0,$roles),
			"chains" => $this->picker(0,$chains),
			"groups" => $this->multiple_option_list(array(),$groups),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent))
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_ACL
			));
		}

		$meta = $this->get_object_metadata(array(
			"oid" => $id
		));
		
		$groups = $this->make_keys($groups);

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => array(
				"role" => $role,
				"chain" => $chain,
				"groups" => $groups,
				"priority" => $priority
			)
		));

		$ags = array();
		if (is_array($meta["added_groups"]))
		{
			foreach($meta["added_groups"] as $gid => $gdata)
			{
				if ($groups[$gid] != $gid)
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
		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "added_groups",
			"value" => $ags
		));

		if ($save_acl)
		{
			// right. the tricky bit. we must read all the objects that are selected in the object chain and
			// for those objects set their acl as is defined in the role 
			// for all groups selected.
			$this->update_acl($id);
		}
		return $this->mk_my_orb("change", array("id" => $id));
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

		$gads = $meta["added_groups"];
		foreach($objs->get() as $oid)
		{
			$o_grps = $this->get_acl_groups_for_obj($oid);
			foreach($meta["groups"] as $grp)
			{
				// ok, before we do this, we must check if another acl object, that
				// has a higher priority, includes this folder<->group relation
				// and if one is found, then we must not set the acl.
				if (!$this->higher_priority_acl_exists($meta["priority"], $oid, $grp))
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

	function change($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$o = $this->get_object($id);
		$this->mk_path($o["parent"],"Muuda ACLi");

		$meta = $this->get_object_metadata(array(
			"metadata" => $o["metadata"]
		));

		$roles = $this->list_objects(array(
			"class" => CL_ROLE
		));

		$chains = $this->list_objects(array(
			"class" => CL_OBJECT_CHAIN
		));

		$u = get_instance("users");
		$groups = $u->get_group_picker(array(
			"type" => array(GRP_REGULAR,GRP_DEFAULT,GRP_DYNAMIC,GRP_USERGRP)
		));

		$this->vars(array(
			"name" => $o["name"],
			"roles" => $this->picker($meta["role"],$roles),
			"chains" => $this->picker($meta["chain"],$chains),
			"groups" => $this->multiple_option_list($meta["groups"],$groups),
			"priority" => $meta["priority"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));
		return $this->parse();
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

		foreach($this->acl_object_cache as $oid => $obj)
		{
			if ($obj->relation_exists($oid, $grp) && $obj->get_priority() > $cur_acl)
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
			foreach($meta["groups"] as $_grp)
			{
				if ($oid == $_oid && $grp == $_grp)
				{
					return true;
				}
			}
		}
		return false;
	}
}
?>