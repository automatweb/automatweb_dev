<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/accessmgr.aw,v 2.16 2004/02/12 11:48:03 kristo Exp $

class accessmgr extends aw_template
{
	function accessmgr()
	{
		$this->init("accessmgr");
		$this->sub_merge = 1;
		lc_load("definition");
		$this->lc_load("accessmsg","lc_accessmsg");

		$this->ar = aw_unserialize($this->get_cval("accessmgr"));
		if (!is_array($this->ar))
		{
			$this->_do_init_accessmgr();
			$s = aw_serialize($this->ar);
			$this->quote(&$s);
			$this->set_cval("accessmgr", $s);
		}
	}

	/**  
		
		@attrib name=list_access params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function list_access($arr)
	{
		$this->read_template("list.tpl");

		$this->vars(array(
			"name" => "K&otilde;ik", 
			"oid" => $this->ar["root"]
		));
		$this->parse("ACL");
		$this->parse("LINE");
		reset($this->cfg["programs"]);
		while (list($prid, $ar) = each($this->cfg["programs"]))
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
			$id = $this->new_object(array(
				"parent" => $this->ar["root"], 
				"class_id" => CL_ACCESSMGR, 
				"status" => $prid, 
				"name" => $this->cfg["programs"][$prid]["name"]
			));
			$this->ar[$prid] = $id;
			$s = aw_serialize($this->ar);
			$this->quote(&$s);
			$this->set_cval("accessmgr", $s);
		}
	}

	function on_site_init(&$dbi, &$site, &$ini_opts, &$log, &$osi_vars)
	{
		if ($site['site_obj']['use_existing_templates'])
		{
			return;
		}
		$this->dc = $dbi->dc;
		$this->_do_init_accessmgr();
		$s = aw_serialize($this->ar);
		$this->quote(&$s);
		$dbi->db_query("INSERT INTO config(ckey,content) values('accessmgr','$s')");

		// now, clear all acls for these objects and give all perms to admin grp and none for all users
		$this->db_query("DELETE FROM acl WHERE oid IN (".join(",", array_values($this->ar)).")");

		$dbi->add_acl_group_to_obj(2, $this->ar["root"]);
		$dbi->save_acl($this->ar["root"], 2,array(
			"can_edit" => 1,
			"can_add" => 1,
			"can_admin" => 1,
			"can_delete" => 1,
			"can_view" => 1
		));
	}

	function _do_init_accessmgr()
	{
		$minoid = $this->db_fetch_field("SELECT MIN(oid) AS minoid FROM objects", "minoid");
		$this->ar = array();
		// loome k6ik vajalikud objektid
		// k6igepealt root objekti et saax k6igile 6igusi m22rata
		$id = $this->new_object(array(
			"parent" => $minoid,
			"class_id" => CL_ACCESSMGR, 
			"status" => 0,
			"name" => "Accessmgr",
			"no_flush" => 1
		));
		$this->ar["root"] = $id;

		reset($this->cfg["programs"]);
		while (list($prid, $ar) = each($this->cfg["programs"]))
		{
			$id = $this->new_object(array(
				"parent" => $this->ar["root"], 
				"class_id" => CL_ACCESSMGR, 
				"status" => $prid,
				"name" => $ar["name"],
				"no_flush" => 1
			));
			$this->ar[$prid] = $id;
		}
	}
}
?>
