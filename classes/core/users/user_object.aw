<?php
/*
@classinfo  maintainer=kristo
*/

class user_object extends _int_object
{
	function prop($k)
	{
		if ($k === "password")
		{
			return "";
		}
		$rv = parent::prop($k);
		if($k === "history_size")
		{
			if(!($rv > "-1"))
			{
				return 25;
			}
		}
		return $rv;
	}

	function set_prop($k, $v)
	{
		if ($k !== "password")
		{
			parent::set_prop($k,$v);
		}
	}

	function save($exclusive = false, $previous_state = null)
	{
		$new = !is_oid($this->id());
		$rv = parent::save($exclusive, $previous_state);

		if ($new)
		{
			$this->_handle_user_create();
		}
		return $rv;
	}

	private function _handle_user_create()
	{
		// create home folder
		aw_disable_acl();
		$o = obj();
		$o->set_class_id(CL_MENU);
		$o->set_parent(1);
		$o->set_name($this->prop("uid"));
		$o->set_comment(sprintf(t("%s kodukataloog"), $this->prop("uid")));
		$o->set_prop("type", MN_HOME_FOLDER);
		$hfid = $o->save();
		$this->set_prop("home_folder", $hfid);
		$this->save();

		// create default group
		// in the bloody eau database the object with oid 1 is the groups folder. bloody hell.
		// this really needs a better solution :(
		$gid = obj(get_instance(CL_GROUP)->add_group((aw_ini_get("site_id") == 65 ? 5 : 1), $this->prop("uid"), GRP_DEFAULT, USER_GROUP_PRIORITY));

		$i = get_instance(CL_MENU);

		// give all access to the home folder for this user
		$i->create_obj_access($hfid,$this->prop("uid"));
		// and remove all access from everyone else
		$i->deny_obj_access($hfid);

		aw_restore_acl();

		// user has all access to itself
		$i->create_obj_access($this->id(),$this->prop("uid"));
	}

	/** sets the user's password
		@attrib api=1 params=pos

		@param pwd required type=string
			The password to set

		@comment
			you can't set the users password via set_prop for security reasons, you must use this method. you will need to save the object after calling this as well
	**/
	function set_password($pwd)
	{
		if (aw_ini_get("auth.md5_passwords"))
		{
			$pwd = md5($pwd);
		}
		parent::set_prop("password", $pwd);
	}

	/** returns an array of group objects of whom this user is a member of
		@attrib api=1

		@returns
			array { group_oid => group_obj, ... } for all groups that this user is a member of
	**/
	function get_groups_for_user()
	{
		$ol = get_instance(CL_USER)->get_groups_for_user(parent::prop("uid"));
		$rv = $ol->arr();
		// now, the user's own group is not in this list probably, so we go get that as well
		$ol = new object_list(array(
			"class_id" => array(CL_GROUP, CL_USER_GROUP),
			"name" => $this->name(),
			"lang_id" => array(),
			"site_id" => array(),
			"type" => GRP_DEFAULT
		));
		if ($ol->count())
		{
			$mg = $ol->begin();
			$rv[$mg->id()] = $mg;
		}
		uasort($rv, array(&$this, "_pri_sort"));
		return $rv;
	}

	/** returns the user's default group oid
		@attrib api=1

		@returns
			oid of the user's default group or null if it not found.
	**/
	function get_default_group()
	{
		static $cache;
		if ($cache)
		{
			return $cache;
		}
		// now, the user's own group is not in this list probably, so we go get that as well
		$ol = new object_list(array(
			"class_id" => array(CL_GROUP, CL_USER_GROUP),
			"name" => $this->name(),
			"lang_id" => array(),
			"site_id" => array(),
			"type" => GRP_DEFAULT
		));
		if ($ol->count())
		{
			$mg = $ol->begin();
			$cache = $mg->id();
			return $mg->id();
		}
		return null;
	}

	private function _pri_sort($a, $b)
	{
		return $b->prop("priority") - $a->prop("priority");
	}

	/**
	@attrib name=generate_password params=pos api=1

	@param lenght optional type=int
		Default value 8.

	**/
	function generate_password($lenght = 8)
	{
		$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy";
		$p = "";
		for($i = 0; $i < $lenght; $i++)
		{
			$rand = rand(0, strlen($chars) - 1);
			$p .= $chars[$rand];
		}
		return $p;
	}

	function create_brother($p)
	{
		$rv = parent::create_brother($p);
		if(obj($p)->class_id() == CL_GROUP)
		{
			// If you save user under group, the user must be added into that group!
			get_instance(CL_GROUP)->add_user_to_group(obj(parent::id()), obj($p), array("brother_done" => true));
		}
		return $rv;
	}

	public function is_group_member($user, $group)
	{
		$user = is_object($user) ? $user : (is_oid($user) ? obj($user) : $this);
		$group = is_object($group) ? array($group->id()) : (array) $group;

		$grps = $user->get_groups_for_user();
		return count(array_intersect($group, array_keys($grps))) > 0;
	}

	/**
	@attrib name=get_user_name api=1
	@returns string
	**/
	public function get_user_name()
	{
		if($this->prop("real_name"))
		{
			return $this->prop("real_name");
		}
		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());
		return $p->name();
	}

	/**
	@attrib name=get_user_mail_address api=1
	@returns string
	**/
	public function get_user_mail_address()
	{
		if($this->prop("email"))
		{
			return $this->prop("email");
		}
		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());
		return $p->get_mail();
	}
}

?>
