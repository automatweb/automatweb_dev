<?php

/*

@classinfo syslog_type=ST_ROLE 

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

@property save_acl_sep type=text store=no no_caption=1
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
		$o = obj($role);
		return $o->meta("acls");
	}

	function get_acl_values($role)
	{
		$o = obj($role);

		$ret = array();
		$acls = new aw_array($o->meta("acls"));
		$acls_set = $o->meta("acls_set");

		foreach($acls->get() as $aclname)
		{
			$ret[$aclname] = ($acls_set[$aclname] == $aclname ? aw_ini_get("acl.allowed") : aw_ini_get("acl.denied"));
		}
		return $ret;
	}

	function get_changeables($arr)
	{
		$acls = array();
		$a = $this->acl_list_acls();

		$m_a = $arr["obj_inst"]->meta("acls");

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
				'value' => $m_a[$a_name] == $a_name
			);
		}
		return $acls;
	}

	function get_acls($arr)
	{
		$acls = array();
		$a = $this->acl_list_acls();

		$a_s = $arr["obj_inst"]->meta("acls");
		$a_s_s = $arr["obj_inst"]->meta("acls_set");

		foreach($a as $a_bp => $a_name)
		{
			if ($a_s[$a_name] == $a_name)
			{
				$rt = "acl_set_".$a_bp;
				$acls[$rt] = array(
					'name' => $rt,
					'caption' => $a_name,
					'type' => 'checkbox',
					'ch_value' => 1,
					'store' => 'no',
					'group' => 'objects',
					'value' => $a_s_s[$a_name] == $a_name
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
				$acls = array();

				$a = $this->acl_list_acls();
				foreach($a as $a_bp => $a_name)
				{
					if ($arr["request"]["acl_".$a_bp] == 1)
					{
						$acls[$a_name] = $a_name;
					}
				}
				$arr["obj_inst"]->set_meta("acls",$acls);
				break;

			case 'acls':
				$acls_set = array();
				$a = $this->acl_list_acls();
				foreach($a as $a_bp => $a_name)
				{
					if ($arr["request"]["acl_set_".$a_bp] == 1)
					{
						$acls_set[$a_name] = $a_name;
					}
				}
				$arr["obj_inst"]->set_meta("acls_set",$acls_set);
				break;

			case 'save_acl':
				if ($arr["request"]["save_acl"] == 1)
				{
					$ac = get_instance("core/acl/acl_class");
					$rows = $ac->get_acls_for_role($arr["obj_inst"]->id());
					foreach($rows as $acid)
					{
						$ac->update_acl($acid);
					}
				}
				break;
		}
		return PROP_OK;
	}

}
?>
