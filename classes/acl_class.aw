<?php

classload("users","object_chain","role");
class acl_class extends aw_template
{
	function acl_class()
	{
		enter_function("acl_class::acl_class",array());
		$this->init("automatweb/acl");
		exit_function("acl_class::acl_class");
	}

	function add($arr)
	{
		enter_function("acl_class::add",array());
		extract($arr);
		$this->read_template("add.tpl");
		$this->mk_path($parent,"Lisa ACL");

		$roles = $this->list_objects(array(
			"class" => CL_ROLE
		));

		$chains = $this->list_objects(array(
			"class" => CL_OBJECT_CHAIN
		));

		$u = new users;
		$groups = $u->get_group_picker(array(
			"type" => array(GRP_REGULAR,GRP_DEFAULT,GRP_DYNAMIC,GRP_USERGRP)
		));

		$this->vars(array(
			"roles" => $this->picker(0,$roles),
			"chains" => $this->picker(0,$chains),
			"groups" => $this->multiple_option_list(array(),$groups),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent))
		));
		exit_function("acl_class::add");
		return $this->parse();
	}

	function submit($arr)
	{
		enter_function("acl_class::submit",array());
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
		exit_function("acl_class::submit");
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function update_acl($id)
	{
		enter_function("acl_class::update_acl",array());
		$o = $this->get_object($id);
		$meta = $this->get_object_metadata(array(
			"metadata" => $o["metadata"]
		));

		$oc = new object_chain;
		$objs = $oc->get_objects_in_chain($meta["chain"]);

		$ro = new role;
		$mask = $ro->get_acl_mask($meta["role"]);
		$aclarr = $ro->get_acl_values($meta["role"]);

		$gads = $meta["added_groups"];
		foreach($objs as $oid)
		{
			$o_grps = $this->get_acl_groups_for_obj($oid);
			foreach($meta["groups"] as $grp)
			{
				if (!isset($o_grps[$grp]))
				{
					$this->add_acl_group_to_obj($grp,$oid);
					$gads[$grp][$oid] = $grp;
				}

				$this->save_acl_masked($oid,$grp,$aclarr,$mask);
			}
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "added_groups",
			"value" => $gads
		));
		exit_function("acl_class::update_acl");
	}

	function change($arr)
	{
		enter_function("acl_class::change",array());
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

		$u = new users;
		$groups = $u->get_group_picker(array(
			"type" => array(GRP_REGULAR,GRP_DEFAULT,GRP_DYNAMIC,GRP_USERGRP)
		));

		$this->vars(array(
			"name" => $o["name"],
			"roles" => $this->picker($meta["role"],$roles),
			"chains" => $this->picker($meta["chain"],$chains),
			"groups" => $this->multiple_option_list($meta["groups"],$groups),
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));
		exit_function("acl_class::change");
		return $this->parse();
	}

	function get_acls_for_role($role)
	{
		enter_function("acl_class::get_acls_for_role",array());
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
		exit_function("acl_class::get_acls_for_role");
		return $ret;
	}
}
?>
