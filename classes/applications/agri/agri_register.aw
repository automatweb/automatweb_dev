<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/agri/Attic/agri_register.aw,v 1.1 2004/03/28 21:45:14 kristo Exp $
// agri_register.aw - Agri login 
/*

@classinfo syslog_type=ST_AGRI_REGISTER relationmgr=yes

@default table=objects
@default group=general

*/

class agri_register extends class_base
{
	function agri_register()
	{
		$this->init(array(
			"tpldir" => "applications/agri/agri_register",
			"clid" => CL_AGRI_REGISTER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_login")
		));
		return $this->parse();
	}

	/** checks if the user exists, if so, asks password, else redirects to user add func

		@attrib name=submit_login nologin="1"

	**/
	function submit_login($arr)
	{
		extract($arr);

		if (empty($utype) || empty($regcode))
		{
			$this->read_template("err_login.tpl");
			return $this->parse();
		}

		$ol = new object_list(array(
			"class_id" => CL_USER,
			"name" => "agri_".$regcode
		));

		if ($ol->count() > 0)
		{
			return $this->mk_my_orb("verify_pwd", array("utype" => $utype, "regcode" => $regcode));
		}
		return $this->mk_my_orb("register_user", array("utype" => $utype, "regcode" => $regcode));
	}

	/** asks the user for a password

		@attrib name=verify_pwd nologin="1"

		@param utype required 
		@param regcode required

	**/
	function verify_pwd($arr)
	{
		extract($arr);
		$this->read_template("verify_pwd.tpl");

		$this->vars(array(
			"utype" => ($utype == "pri" ? "&uuml;ksikisik" : "juriidiline isik"),
			"regcode" => $regcode,
			"reforb" => $this->mk_reforb("submit_verify_pwd", array("utype" => $utype, "regcode" => $regcode))
		));

		return $this->parse();
	}

	/** checks if the password is correcdt and logs in the user

		@attrib name=submit_verify_pwd nologin="1"

	**/
	function submit_verify_pwd($arr)
	{
		extract($arr);

		if (empty($utype) || empty($regcode) || empty($pass))
		{
			$this->read_template("err_login.tpl");
			return $this->parse();
		}

		$ol = new object_list(array(
			"class_id" => CL_USER,
			"name" => "agri_".$regcode
		));

		if ($ol->count() > 0)
		{
			$o = $ol->begin();
			$u_pwd = $this->db_fetch_field("SELECT password FROM users WHERE uid = 'agri_".$regcode."'", "password");
			if ($u_pwd == md5($pass) && $o->prop("blocked") != 1)
			{
				// seems the user is ok, log in!
				$u = get_instance("users");
				$u->login(array(
					"uid" => "agri_".$regcode,
					"password" => $pass
				));

				// now, get the agri_data object that the user has
				$a_c = reset($o->connections_from(array("class" => CL_AGRI_DATA)));
				if (!$a_c)
				{
					error::throw(array(
						"id" => ERR_NO_AGRI_DATA,
						"msg" => "agri_register::submit_verify_pwd($arr): no agri data object connected to user!"
					));
				}
				else
				{
					$a = $a_c->to();
				}

				return $this->mk_my_orb("change", array("id" => $a->id()), CL_AGRI_DATA);
			}
		}

		$this->read_template("err_login.tpl");
		return $this->parse();
	}


	/** adds the user and shows password

		@attrib name=register_user nologin="1"

		@param utype required 
		@param regcode required

	**/
	function register_user($arr)
	{
		extract($arr);

		$ol = new object_list(array(
			"class_id" => CL_USER,
			"name" => "agri_".$regcode
		));

		$this->read_template("register_user.tpl");

		if ($ol->count() < 1)	// protection for reload
		{
			// create user. 
			$u = get_instance("core/users/user");
			$user = $u->add_user(array(
				"uid" => "agri_".$regcode,
			));

			$g = get_instance("core/users/group");
			$g->add_user_to_group($user, obj(aw_ini_get("agri.group")));

			// log in user
			$u = get_instance("users");
			$u->login(array(
				"uid" => "agri_".$regcode,
				"password" => $user->prop("password")
			));

			// create the data object for user
			$do = obj();
			$do->set_class_id(CL_AGRI_DATA);
			$do->set_parent(aw_ini_get("agri.parent"));
			$do->set_name($regcode);
			$do->set_prop("regcode",$regcode);
			$do->set_prop("utype", $utype);
			$do->save();

			// connect
			$user->connect(array(
				"to" => $do->id()
			));

			$this->vars(array(
				"pass" => $user->prop("password"),
			));
		}
		else
		{
			$o = $ol->begin();
			// now, get the agri_data object that the user has
			$a_c = reset($o->connections_from(array("class" => CL_AGRI_DATA)));
			if (!$a_c)
			{
				error::throw(array(
					"id" => ERR_NO_AGRI_DATA,
					"msg" => "agri_register::submit_verify_pwd($arr): no agri data object connected to user!"
				));
			}
			else
			{
				$do = $a_c->to();
			}
		}


		$this->vars(array(
			"utype" => ($utype == "pri" ? "&uuml;ksikisik" : "juriidiline isik"),
			"regcode" => $regcode,
			"link" => $this->mk_my_orb("change", array("id" => $do->id()), CL_AGRI_DATA)
		));

		return $this->parse();
	}

	/** this must set the content for subtemplates in main.tpl
		
		@param inst - instance to set variables to
		@param content_for - array of templates to get content for
	**/
	function on_get_subtemplate_content($arr)
	{
		if (aw_global_get("uid") == "")
		{
			return;
		}

		$us = get_instance("users");
		$o = obj($us->get_oid_for_uid(aw_global_get("uid")));

		$a_c = reset($o->connections_from(array("class" => CL_AGRI_DATA)));
		if (!$a_c)
		{
			return "";
		}
		else
		{
			$do = $a_c->to();
		}

		$inst =& $arr["inst"];
		$inst->vars(array(
			"utype" => ($do->prop("utype") == "pri" ? "&uuml;ksikisik" : "juriidiline isik"),
			"regcode" => $do->prop("regcode")
		));
		$inst->vars(array(
			"AGRI_INF" => $inst->parse("AGRI_INF")
		));
	}
}
?>
