<?php
// $Header: /home/cvs/automatweb_dev/classes/core/users/auth/auth_config.aw,v 1.8 2004/11/29 13:02:08 kristo Exp $
// auth_config.aw - Autentimise Seaded 
/*

@classinfo syslog_type=ST_AUTH_CONFIG relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@groupinfo servers caption=Autentimine

@property servers type=table group=servers no_caption=1

@groupinfo activity caption=Aktiivsus

@property activity type=table group=activity no_caption=1
@caption Aktiivsus

@reltype AUTH_SERVER value=1 clid=CL_AUTH_SERVER_LDAP,CL_AUTH_SERVER_LOCAL
@caption autentimisserver

*/

class auth_config extends class_base
{
	function auth_config()
	{
		$this->init(array(
			"tpldir" => "core/users/auth/auth_config",
			"clid" => CL_AUTH_CONFIG
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "activity":
				$this->mk_activity_table($arr);
				break;

			case "servers":
				$this->do_servers($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "activity":
				$ol = new object_list(array(
					"class_id" => CL_AUTH_CONFIG,
					"lang_id" => array(),
					"site_id" => array()
				));
				for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
				{
					if ($o->flag(OBJ_FLAG_IS_SELECTED) && $o->id() != $arr["request"]["active"])
					{
						$o->set_flag(OBJ_FLAG_IS_SELECTED, false);
						$o->save();
					}
					else
					if ($o->id() == $arr["request"]["active"] && !$o->flag(OBJ_FLAG_IS_SELECTED))
					{
						$o->set_flag(OBJ_FLAG_IS_SELECTED, true);
						$o->save();
					}
				}
				break;

			case "servers":
				$arr["obj_inst"]->set_meta("auth", $arr["request"]["data"]);
				break;
		}
		return $retval;
	}	

	function mk_activity_table($arr)
	{
		// this is supposed to return a list of all authconfigs
		// to let the user choose the active one
		$table = &$arr["prop"]["vcl_inst"];
		$table->parse_xml_def("activity_list");

		$pl = new object_list(array(
			"class_id" => CL_AUTH_CONFIG,
			"site_id" => array(),
			"lang_id" => array()
		));	
		for($o = $pl->begin(); !$pl->end(); $o = $pl->next())
		{
			$actcheck = checked($o->flag(OBJ_FLAG_IS_SELECTED));
			$act_html = "<input type='radio' name='active' $actcheck value='".$o->id()."'>";
			$row = $o->arr();
			$row["active"] = $act_html;
			$table->define_data($row);
		};
	}

	function _init_servers_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "type",
			"caption" => "Serveri t&uuml;&uuml;p",
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "use",
			"caption" => "Kasuta?",
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "jrk",
			"caption" => "J&auml;rjekord",
			"align" => "center"
		));
	}

	function do_servers($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_servers_tbl($t);

		$clss = aw_ini_get("classes");		

		$data = $arr["obj_inst"]->meta("auth");

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_AUTH_SERVER")) as $c)
		{
			$serv = $c->to();
			$t->define_data(array(
				"type" => $clss[$serv->class_id()]["name"],
				"name" => $serv->name(),
				"use" => html::checkbox(array(
					"name" => "data[".$serv->id()."][use]",
					"value" => 1,
					"checked" => ($data[$serv->id()]["use"] == 1)
				)),
				"jrk" => html::textbox(array(
					"name" => "data[".$serv->id()."][jrk]",
					"value" => $data[$serv->id()]["jrk"],
					"size" => 4
				)),
				"hidden_jrk" => $data[$serv->id()]["jrk"]
			));
		}

		$t->set_default_sortby("hidden_jrk");
		$t->sort_by();
	}

	/** checks if any auth config is active and if so, returns it's id

		@attrib api=1
	**/
	function has_config()
	{
		$ol = new object_list(array(
			"class_id" => CL_AUTH_CONFIG,
			"flags" => array(
				"mask" => OBJ_FLAG_IS_SELECTED,
				"flags" => OBJ_FLAG_IS_SELECTED
			),
			"lang_id" => array(),
			"site_id" => array()
		));
		if ($ol->count())
		{
			$tmp = $ol->begin();
			return $tmp->id();
		}
		return false;
	}

	/** authenticates the given user agains the given authentication config

		@attrib api=1

		@comment
			auth_id - the config id to auth against
			credentials - array of uid => username, password => password for the user to be authenticated
	**/
	function check_auth($auth_id, $credentials)
	{
		// get list of servers, sort by order and try each one
		$servers = $this->_get_auth_servers($auth_id);

		foreach($servers as $server)
		{
			$server_inst = get_instance($server->class_id());
			list($is_valid, $msg, $break_chain) = $server_inst->check_auth($server, $credentials, $this);

			if ($is_valid)
			{
				return array(true, $msg);
			}
			else
			if ($break_chain)
			{
				break;
			}
		}	
		if ($msg == "")
		{
			$msg = "Sellist kasutajat pole!";
		}
		return array(false, $msg);
	}

	/** returns sorted array of server id's
	**/
	function _get_auth_servers($id)
	{
		if (!is_oid($id) || !$this->can("view", $id))
		{
			return array();
		}
		$o = obj($id);
		$s = $this->_get_server_list($o);
		asort($s);
		$tmp = array_keys($s);
		$ret = array();
		foreach($tmp as $sid)
		{
			$ret[] = obj($sid);
		}
		return $ret;
	}

	/** returns array id => jrk
	**/
	function _get_server_list($o)
	{
		$ret = array();
		$s = $o->meta("auth");
		foreach($o->connections_from(array("type" => "RELTYPE_AUTH_SERVER")) as $c)
		{
			$to_id = $c->prop("to");
			if ($s[$to_id]["use"] == 1)
			{
				$ret[$to_id] = $s[$to_id]["jrk"];
			}
		}
		return $ret;
	}

	/** checks if the given local user exists and if not, creates it

		@attrib api=1

	**/
	function check_local_user($auth_id, $cred)
	{
		$ol = new object_list(array(
			"class_id" => CL_USER,
			"name" => $cred["uid"],
			"site_id" => array(),
			"lang_id" => array(),
			"brother_of" => new obj_predicate_prop("id")
		));

		$confo = obj($auth_id);

		if ($ol->count())
		{
			// check e-mail and name if present in $cred
			if (!empty($cred["mail"]) || !empty($cred["name"]))
			{
				$u = $ol->begin();
				$this->_upd_udata($u, $cred, $confo);
			}
			return true;
		}

		if (!$confo->prop("auto_create_user"))
		{
			return false;
		}

		aw_disable_acl();
		// create local user
		$us = get_instance(CL_USER);
		$new_user = $us->add_user(array(
			"uid" => $cred["uid"],
			"password" => $cred["password"]
		));

		$this->_upd_udata($new_user, $cred, $confo);

		aw_restore_acl();
		return true;
	}

	/** Generates the login form 
		
		@attrib name=show_login params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function show_login($args = array())
	{
		$this->read_adm_template("login.tpl");
		// remember the uri used before login so that we can 
		// redirect the user back there after (and if) he/she has finally
		// logged in
		global $request_uri_before_auth;
		$request_uri_before_auth = aw_global_get("REQUEST_URI");
		session_register("request_uri_before_auth");
		$this->vars(array(
			"reforb" => $this->mk_reforb("login",array(),'users'),
		));
		return $this->parse();
	}

	/** if the current page requires login, then remember the url, ask for login and put the user back

	**/
	function redir_to_login()
	{
		global $request_uri_before_auth;
		$request_uri_before_auth = aw_global_get("REQUEST_URI");
		session_register("request_uri_before_auth");
		header("Location: ".aw_ini_get("baaseurl")."/login.".aw_ini_get("ext"));
		die();
	}

	function _upd_udata($u, $cred, $confo)
	{
		aw_disable_acl();
		$u->set_prop("email", $cred["mail"]);
		$u->save();

		$users = get_instance("users");
		$users->set_user_config(array(
			"uid" => $cred["uid"],
			"key" => "real_name",
			"value" => $cred["name"]
		));

		// get group from auth conf
		if (($grp = $confo->prop("no_user_grp")))
		{
			// add to group
			$gp = get_instance(CL_GROUP);
			$gp->add_user_to_group($u, obj($grp));
		}

		aw_restore_acl();
	}
}
?>
