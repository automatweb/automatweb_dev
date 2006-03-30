<?php
// $Header: /home/cvs/automatweb_dev/classes/core/acl/Attic/acl_class.aw,v 1.3 2006/03/30 07:10:28 kristo Exp $
/* 

@classinfo syslog_type=ST_ACL relationmgr=yes

@default group=general
@default table=objects
@default field=meta
@default method=serialize

@property chain type=relpicker reltype=RELTYPE_CHAIN
@caption Vali p&auml;rg

@property role type=relpicker reltype=RELTYPE_ROLE
@caption Vali roll

@property groups type=relpicker multiple=1 reltype=RELTYPE_GROUP
@caption Vali grupid

@property priority type=textbox
@caption Prioriteet

@property update_acl type=checkbox ch_value=1 store=no 
@caption Uuenda ACL

@reltype CHAIN value=1 clid=CL_OBJECT_CHAIN
@caption pärg

@reltype ROLE value=2 clid=CL_ROLE
@caption roll

@reltype GROUP value=3 clid=CL_GROUP
@caption grupp

*/

class acl_class extends class_base
{
	function acl_class()
	{
		$this->init(array(
			"clid" => CL_ACL
		));
	}

	function update_acl($id)
	{
		$o = new object($id);
		// cannot operate if those are not, so bail out
		if (!is_oid($o->prop("chain")) || !is_oid($o->prop("role")))
		{
			return false;	
		};

		error::raise(array(
			"id" => "ERR_NOIMPL",
			"msg" => t("This function needs updating (get_acl_groups_for_obj)")
		));
		$meta = $o->meta();

		$oc = get_instance(CL_OBJECT_CHAIN);
		$objs = new aw_array($oc->get_objects_in_chain($o->prop("chain")));

		$ro = get_instance(CL_ROLE);
		$mask = $ro->get_acl_mask($o->prop("role"));
		$aclarr = $ro->get_acl_values($o->prop("role"));

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
			$mg = new aw_array($o->prop("groups"));
			foreach($mg->get() as $grp)
			{
				$grp = $u->get_gid_for_oid($grp);

				// ok, before we do this, we must check if another acl object, that
				// has a higher priority, includes this folder<->group relation
				// and if one is found, then we must not set the acl.
				if (!$this->higher_priority_acl_exists($id,$o->prop("priority"), $oid, $grp))
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

		$o->set_meta("added_groups",$gads);
	}

	function get_acls_for_role($role)
	{
		$objs = new object_list(array(
			"class_id" => CL_ACL,
			"lang_id" => array(),
		));
	
		$ret = array();
		for($o = $objs->begin(); !$objs->end(); $o = $objs->next())
		{
			if ($o->prop("role") == $role)
			{
				$ret[$o->id()] = $o->id();
			}
		}
		return $ret;
	}

	function higher_priority_acl_exists($cur_acl, $cur_priority, $oid, $grp)
	{
		if (!isset($this->acl_object_cache))
		{
			$this->acl_object_cache = array();
			$ol = new object_list(array(
				"class_id" => CL_ACL,
				"lang_id" => array(),
			));
			for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				if ($o->id() != $cur_acl)
				{
					$this->acl_object_cache[$o->id()] = new acl_class;
					$this->acl_object_cache[$o->id()]->acl_obj = $o;
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
		return $this->acl_obj->prop("priority");
	}

	function relation_exists($oid, $grp)
	{
		$oc = get_instance(CL_OBJECT_CHAIN);
		$objs = new aw_array($oc->get_objects_in_chain($this->acl_obj->prop("chain")));

		foreach($objs->get() as $_oid)
		{	
			$gpa = new aw_array($this->acl_obj->prop("groups"));
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
		$objs = new object_list(array(
			"class_id" => CL_ACL,
			"lang_id" => array(),
		));

		$ret = array();
		for($o = $objs->begin(); !$objs->end(); $o = $objs->next())
		{
			$grps = $o->prop("groups");
			if ($grps[$gid] == $gid)
			{
				$ret[$o->id()] = $o->id();
			}
		}
		return $ret;
	}

	function get_roles_for_acl($oid)
	{
		$ob = new object($oid);
		return $ob->prop("role");
	}

	////
	// !removes group $gid from acl object $acl
	function remove_group_from_acl($acl, $gid)
	{
		$ob = new object($acl);
		$grps = $ob->prop("groups");
		unset($grps[$gid]);
		$ob->set_prop("groups",$grps);
	}

	function set_property($arr)
	{
		$prop =& $arr["prop"];

		$meta = $arr["obj_inst"]->meta();

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
				break;

			case "update_acl":
				if ($arr["request"]["update_acl"] == 1 && is_oid($arr["obj_inst"]->id()))
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
			$this->update_acl($arr["obj_inst"]->id());
		}
	}
}
?>
