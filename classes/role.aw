<?php

/*

@classinfo syslog_type=ST_ROLE relationmgr=yes

@groupinfo general caption=Üldine
@groupinfo acls caption="Lubatud &otilde;igused"

@default group=general
@default table=objects

@property changes_desc type=text store=no
@caption Muudetavad &otilde;igused

@property changes type=callback callback=get_changeables store=no
@caption Muudetavad &otilde;igused

@default group=acls

@property acls type=callback callback=get_acls store=no
@caption Lubatud

@property save_acl_sep type=text store=no no-caption=1
@caption 

@property save_acl type=checkbox ch_value=1 store=no
@caption Uuenda ACL


*/

class role extends class_base
{
	function role()
	{
		$this->init(array(
			"tpldir" => "role",
			"clid" => CL_ROLE
		));
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
		$acls = new aw_array($meta["acls"]);
		foreach($acls->get() as $aclname)
		{
			$ret[$aclname] = ($meta["acls_set"][$aclname] == $aclname ? aw_ini_get("acl.allowed") : aw_ini_get("acl.denied"));
		}
		return $ret;
	}

	function get_changeables($arr)
	{
		$acls = array();
		$a = $this->acl_list_acls();
		foreach($a as $a_bp => $a_name)
		{
			$rt = "acl_".$a_bp;
			$acls[$rt] = array(
				'name' => $rt,
				'caption' => $a_name,
				'type' => 'checkbox',
				'ch_value' => 1,
				'store' => 'no',
				'group' => 'objects',
				'value' => $arr["obj"]["meta"]["acls"][$a_name] == $a_name
			);
		}
		return $acls;
	}

	function get_acls($arr)
	{
		$acls = array();
		$a = $this->acl_list_acls();
		foreach($a as $a_bp => $a_name)
		{
			if ($arr["obj"]["meta"]["acls"][$a_name] == $a_name)
			{
				$rt = "acl_set_".$a_bp;
				$acls[$rt] = array(
					'name' => $rt,
					'caption' => $a_name,
					'type' => 'checkbox',
					'ch_value' => 1,
					'store' => 'no',
					'group' => 'objects',
					'value' => $arr["obj"]["meta"]["acls_set"][$a_name] == $a_name
				);
			}
		}
		return $acls;
	}

	function set_property(&$arr)
	{
		$prop =& $arr["prop"];
		switch($prop['name'])
		{
			case 'changes':
				$arr["metadata"]["acls"] = array();
				$a = $this->acl_list_acls();
				foreach($a as $a_bp => $a_name)
				{
					if ($arr["form_data"]["acl_".$a_bp] == 1)
					{
						$arr["metadata"]["acls"][$a_name] = $a_name;
					}
				}
				break;

			case 'acls':
				$arr["metadata"]["acls_set"] = array();
				$a = $this->acl_list_acls();
				foreach($a as $a_bp => $a_name)
				{
					if ($arr["form_data"]["acl_set_".$a_bp] == 1)
					{
						$arr["metadata"]["acls_set"][$a_name] = $a_name;
					}
				}
				break;

			case 'save_acl':
				if ($arr["form_data"]["save_acl"] == 1)
				{
					$ac = get_instance("acl_class");
					$rows = $ac->get_acls_for_role($arr["obj"]["oid"]);
					foreach($rows as $acid)
					{
						$ac->update_acl($acid);
					}
				}
				break;
		}
		return PROP_OK;
	}

/*	function get_property(&$arr)
	{
		if ($arr["prop"]["name"] == "save_acl" && !$arr["obj"]["oid"])
		{
			return PROP_IGNORE;
		}
		return PROP_OK;
	}*/
}
?>
