<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/accessmgr.aw,v 2.7 2002/03/18 20:54:38 kristo Exp $

lc_load("accessmsg");
classload("config");
class accessmgr extends aw_template
{
	function accessmgr()
	{
		$this->db_init();
		$this->tpl_init("accessmgr");
		$this->sub_merge = 1;
		lc_load("definition");
		global $lc_accessmsg;
		if (is_array($lc_accessmsg))
		{
			$this->vars($lc_accessmsg);
		}

		$this->ar = unserialize($this->get_cval("accessmgr"));
		if (!is_array($this->ar))
		{
			$this->ar = array();
			// loome k6ik vajalikud objektid
			// k6igepealt root objekti et saax k6igile 6igusi m22rata
			$id = $this->new_object(array("parent" => 0, "class_id" => CL_ACCESSMGR, "status" => 0,"name" => "Accessmgr"));
			$this->ar["root"] = $id;

			global $programs;
			reset($programs);
			while (list($prid, $ar) = each($programs))
			{
				$id = $this->new_object(array("parent" => $this->ar["root"], "class_id" => CL_ACCESSMGR, "status" => $prid,"name" => $ar[name]));
				$this->ar[$prid] = $id;
			}
			$c = new config;
			$c->create_config("accessmgr", serialize($this->ar));
		}
	}

	function list_access($arr)
	{
		$this->read_template("list.tpl");

		$this->vars(array("name" => "K&otilde;ik", "oid" => $this->ar["root"]));
		$this->parse("ACL");
		$this->parse("LINE");
		global $programs;
		reset($programs);
		while (list($prid, $ar) = each($programs))
		{
			$this->check_obj($prid);
			if (!$this->prog_acl("view", $prid))
			{
				continue;
			}

			$this->vars(array("name" => $ar["name"], "oid" => $this->ar[$prid]));
			$ac = "";
			if ($this->prog_acl("admin", $prid))
			{
				$ac = $this->parse("ACL");
			}
			$this->vars(array("ACL" => $ac));
			$this->parse("LINE");
		}
		return $this->parse();
	}

	////
	// !check if the object for this program exists or not and if it doesn't, then create it. 
	function check_obj($prid)
	{
		if (!$this->get_object($this->ar[$prid]))
		{
			global $programs;
			$id = $this->new_object(array("parent" => $this->ar["root"], "class_id" => CL_ACCESSMGR, "status" => $prid, "name" => $programs[$prid]["name"]));
			$this->ar[$prid] = $id;
			$c = new db_config;
			$c->set_simple_config("accessmgr", serialize($this->ar));
		}
	}

	function check_environment(&$sys, $fix = false)
	{
		$ret = $sys->check_admin_templates("accessmgr", array("list.tpl"));
		return $ret;
	}
}
?>
