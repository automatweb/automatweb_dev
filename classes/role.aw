<?php

classload("acl_base","acl_class");

class role extends aw_template
{
	function role()
	{
		$this->init("role");
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$this->mk_path($parent,"Lisa ACL");

		$acl_ids = aw_ini_get("acl.ids");
		foreach($acl_ids as $bit => $name)
		{
			$this->vars(array(
				"acl_name" => $name
			));
			$acls .= $this->parse("ACLS");
		}

		$this->vars(array(
			"ACLS" => $acls,
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
				"class_id" => CL_ROLE
			));
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "acls",
			"value" => $this->make_keys($acls)
		));

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "acls_set",
			"value" => $this->make_keys($acls_set)
		));

		if ($save_acl)
		{
			$ac = new acl_class;
			$rows = $ac->get_acls_for_role($id);
			foreach($rows as $acid)
			{
				$ac->update_acl($acid);
			}
		}
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$o = $this->get_object($id);
		$this->mk_path($o["parent"],"Muuda rolli");

		$meta = $this->get_object_metadata(array(
			"metadata" => $o["metadata"]
		));
		$acl_ids = aw_ini_get("acl.ids");
		foreach($acl_ids as $bit => $name)
		{
			$this->vars(array(
				"acl_name" => $name,
				"checked" => checked($meta["acls"][$name] == $name),
				"checked_set" => checked($meta["acls_set"][$name] == $name)
			));
			$as = "";
			if ($meta["acls"][$name] == $name)
			{
				$as = $this->parse("ACL_SET");
			}
			$this->vars(array(
				"ACL_SET" => $as
			));
			$acls .= $this->parse("ACLS");
		}

		$this->vars(array(
			"name" => $o["name"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"ACLS" => $acls,
		));
		return $this->parse();
	}

	function get_acl_mask($role)
	{
		$o = $this->get_object($role);
		$meta = $this->get_object_metadata(array(
			"metadata" => $o["metadata"]
		));
		return $meta["acls"];
	}

	function get_acl_values($role)
	{
		$o = $this->get_object($role);
		$meta = $this->get_object_metadata(array(
			"metadata" => $o["metadata"]
		));

		$ret = array();
		foreach($meta["acls"] as $aclname)
		{
			$ret[$aclname] = ($meta["acls_set"][$aclname] == $aclname ? aw_ini_get("acl.allowed") : aw_ini_get("acl.denied"));
		}
		return $ret;
	}
}
?>