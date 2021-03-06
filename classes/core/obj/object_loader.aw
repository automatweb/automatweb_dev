<?php
/*
@classinfo  maintainer=kristo
*/
/*

this message will get called whenever an object is saved and given the class_id as the message type parameter
and the object's id as the "oid" parameter
EMIT_MESSAGE(MSG_STORAGE_SAVE)

this message will get called whenever a new object is created and given class_id as the message type parameter
and the object's id as the "oid" parameter
EMIT_MESSAGE(MSG_STORAGE_NEW)

*/

class _int_object_loader extends core
{
	// private variables, only object system classes can use these
	var $ds; 					// data source
	var $object_member_funcs;	// names of all object class member functions
	var $cfgu;					// cfgutilities instance
	var $cache;					// cache class instance
	var $__aw_acl_cache;		// acl memory cache

	private $acl_ids;
	private static $tmp_id_count = 0;
	private $registered = false;

	function _int_object_loader()
	{
		$this->init();

		$this->__aw_acl_cache = array();
		$this->acl_ids = aw_ini_get("acl.ids");

		$this->all_ot_flds = array_flip(array(
			"parent", "name", "class_id",
			"modified", "created", "status", "lang_id",
			"comment", "modifiedby", "jrk",
			"period", "alias", "periodic",
			"site_id", "metadata",
			"subclass", "flags", "brother_of"
		));

		// init the datasource from the ini file setting
		$datasources = aw_ini_get("objects.default_datasource");
		if ($datasources == "")
		{
			// default to simple mysql
			$datasources = "mysql";
		}

		$dss = array_reverse(explode(",", $datasources));

		classload("core/obj/ds_".$dss[0]);
		$clname = "_int_obj_ds_".$dss[0];
		// the first is the db specific ds, that does not contain anything
		$this->ds = new $clname;

		for ($i = 1; $i < count($dss); $i++)
		{
			classload("core/obj/ds_".$dss[$i]);
			$clname = "_int_obj_ds_".$dss[$i];
			$this->ds = new $clname($this->ds);
		}

		$this->object_member_funcs = get_class_methods("object");
		$this->cfgu = get_instance("cfg/cfgutils");
		$this->cache = get_instance("cache");

		$this->obj_inherit_props_conf = array();
		$fn = aw_ini_get("site_basedir")."/files/obj_inherit_props.conf";
		if (file_exists($fn) && is_readable($fn))
		{
			$f = fopen($fn, "r");
			if ($f)
			{
				$this->obj_inherit_props_conf = safe_array(aw_unserialize(fread($f, filesize($fn)), false, true));
				fclose($f);
			}
		}
	}

	function oid_for_alias($alias)
	{
		// there is no quote here -- duke
		//$this->quote($alias);

		if (substr($alias,-1) === "/")
		{
			$alias = substr($alias,0,-1);
		}

		// if the site defines recursive aliases, then use those
		if (aw_ini_get("menuedit.recursive_aliases"))
		{
			// split the string at "/"
			// and find each alias part under the previous
			$parts = explode("/", $alias);

			$part = array_shift($parts);

			$parent = $this->ds->get_oid_by_alias(array(
				"alias" => $part,
				"site_id" => aw_ini_get("site_id")
			));

			foreach($parts as $part)
			{
				$parent = $this->ds->get_oid_by_alias(array(
					"alias" => $part,
					"site_id" => aw_ini_get("site_id"),
					"parent" => $parent
				));
			}
			return $parent;
		}
		else
		// else just try to match the whole string
		{
			return $this->ds->get_oid_by_alias(array(
				"alias" => $alias,
				"site_id" => aw_ini_get("site_id")
			));
		}
	}

	// returns oid in param, no list!
	function param_to_oid($param)
	{
		if (is_oid($param))
		{
			list($param) = explode(":", $param);
			return $param;
		}
		else
		if (is_string($param))
		{
			$oid = $this->oid_for_alias($param);
			if (!$oid)
			{
				error::raise(array(
					"id" => "ERR_NO_ALIAS",
					"msg" => sprintf(t("object_loader::param_to_oid(%s): no object with alias %s!"), $param, $param)
				));
				return;
			}
			return $oid;
		}
		else
		if (is_object($param))
		{
			if ($param instanceof object || $param instanceof _int_object)
			{
				return $param->id();
			}
		}

		error::raise(array(
			"id" => "ERR_PARAM",
			"msg" => t("object_loader::param_to_oid(): parameter must be either: oid , string (alias) or object instance!")
		));
	}

	// returns array of oids in param
	function param_to_oid_list($param)
	{
		if (is_oid($param))
		{
			return array($param);
		}
		else
		if (is_string($param))
		{
			$oid = $this->oid_for_alias($param);
			if (!$oid)
			{
				error::raise(array(
					"id" => "ERR_NO_ALIAS",
					"msg" => sprintf(t("no object with alias %s!"), $param)
				));
				return;
			}
			return array($oid);
		}
		else
		if (is_object($param))
		{
			if ($param instanceof object || $param instanceof _int_object)
			{
				return array($param->id());
			}
			else
			if ($param instanceof object_list)
			{
				return $param->ids();
			}
			else
			if ($param instanceof object_tree)
			{
				return $param->ids();
			}
		}
		else
		if (is_array($param))
		{
			$res = array();
			foreach($param as $item)
			{
				if (is_object($item) && get_class($item) === "object")
				{
					$res[] = $item->id();
				}
				else
				if (is_oid($item))
				{
					$res[] = $item;
				}
			}
			return $res;
		}

		error::raise(array(
			"id" => "ERR_PARAM",
			"msg" => t("parameter must be either: oid , string (alias), object instance or object list instance!")
		));
		return;
	}

	////
	// !returns temp id for new object
	function load_new_object($objdata = array(), $constructor_args = array())
	{
		// get tmp oid
		if (!isset($objdata["oid"]))
		{ // tmp oid for completely new object
			$cnt = ++self::$tmp_id_count;
			$oid = "new_object_temp_id_{$cnt}";
		}
		else
		{
			$oid = $objdata["oid"];
			unset($objdata["oid"]); // _int_object built to not know its own oid until saved. when this changes, remove.
		}

		// determine class
		if (isset($objdata["class_id"]) and is_class_id($class = $objdata["class_id"]) and isset($GLOBALS["cfg"]["classes"][$class]["object_override"]))
		{
			$class = basename($GLOBALS["cfg"]["classes"][$class]["object_override"]);
		}
		else
		{
			$class = "_int_object";
		}

		$GLOBALS["objects"][$oid] = new $class($objdata, $constructor_args);
		return $oid;
	}

	function load($oid, $constructor_args = array())
	{
		if (!is_oid($oid))
		{
			error::raise(array(
				"id" => "ERR_PARAM",
				"msg" => sprintf(t("object_loader::load(%s): parameter is not object id!"), $oid)
			));
			return;
		}

		if (isset($GLOBALS["objects"][$oid]))
		{
			$ob = $GLOBALS["objects"][$oid];
		}

		if (!isset($ob) || !is_object($ob))
		{
			// check access rights to object
			if (!$GLOBALS["object_loader"]->ds->can("view", $oid))
			{
				$e = new awex_obj_acl("No view access object with id '{$oid}'.");
				$e->awobj_id = $oid;
				throw $e;
			}

			// load object data
			if (isset($GLOBALS["__obj_sys_objd_memc"][$oid]))
			{
				$objdata = $GLOBALS["__obj_sys_objd_memc"][$oid];
				unset($GLOBALS["__obj_sys_objd_memc"][$oid]);
			}
			else
			{
				$objdata = $GLOBALS["object_loader"]->ds->get_objdata($oid);
			}

			// get class
			$class = isset($GLOBALS["cfg"]["classes"][$objdata["class_id"]]["object_override"]) ? basename($GLOBALS["cfg"]["classes"][$objdata["class_id"]]["object_override"]) : "_int_object";
			$objdata["__obj_load_parameter"] = $oid;

			$ref = new $class($objdata, $constructor_args);
			if ($ref->id() === NULL)
			{
				error::raise(array(
					"id" => "ERR_PARAM",
					"msg" => sprintf(t("object_loader::load(%s): no such object!"), $oid)
				));
				return;
			}
			else
			{
				if (!isset($GLOBALS["objects"][$oid]))
				{
					$GLOBALS["objects"][$oid] = $ref;
				}
			}
		}

		// also remove the entry from acl memc cause we dont need it no more
		if (isset($GLOBALS["__obj_sys_acl_memc"][$oid]))
		{
			unset($GLOBALS["__obj_sys_acl_memc"][$oid]);
		}

		return $GLOBALS["objects"][$oid]->id();
	}

	function save($oid, $exclusive = false, $previous_state = null)
	{
		if (!is_object($GLOBALS["objects"][$oid]))
		{
			error::raise(array(
				"id" => "ERR_OBJECT",
				"msg" => sprintf(t("object_loader::save(%s): no object with oid %s exists in the global list"), $oid, $oid)
			));
			return;
		}

		$t_oid = $GLOBALS["objects"][$oid]->save($exclusive, $previous_state);
		if ($t_oid != $oid)
		{
			// relocate the object in the global list
			$GLOBALS["objects"][$t_oid] = $GLOBALS["objects"][$oid];
			$GLOBALS["objects"][$oid] =& $GLOBALS["objects"][$t_oid];
			post_message_with_param("MSG_STORAGE_NEW", $GLOBALS["objects"][$t_oid]->class_id(), array(
				"oid" => $t_oid
			));
		}
		// return the new value, so that the pointers go to the right place.
		//
		// the problem that there might be other pointers to the previous object should not arise,
		// because you probably will not be able to acquire pointers to temp-objects.
		// probably.
		// well, here's to hoping it won't happen!

		post_message_with_param("MSG_STORAGE_SAVE", $GLOBALS["objects"][$t_oid]->class_id(), array(
			"oid" => $t_oid
		));

		static $lastmod_set;
		if (!$lastmod_set)
		{
			if (aw_ini_get("site_show.objlastmod_only_menu"))
			{
				if ($GLOBALS["objects"][$t_oid]->class_id() == CL_MENU)
				{
					// write the current time as last modification time of any object.
					$this->cache->file_set("objlastmod", time());
				}
			}
			else
			{
				// write the current time as last modification time of any object.
				$this->cache->file_set("objlastmod", time());
			}
			$lastmod_set = 1;
		}

		return $t_oid;
	}

	function save_new($oid)
	{
		if (!is_object($GLOBALS["objects"][$oid]))
		{
			error::raise(array(
				"id" => "ERR_OBJECT",
				"msg" => sprintf(t("object_loader::save_new(%s): no object with oid %s exists in the global list"), $oid, $oid)
			));
			return;
		}

		// right. here we need to make a copy BEFORE calling save_new, because
		// otherwise the previous object will get it's oid ovewritten
		$t_o = $GLOBALS["objects"][$oid];
		$t_oid = $t_o->save_new();

		// copy the object to the new place
		$GLOBALS["objects"][$t_oid] = $t_o;
		$this->cache->file_set("objlastmod", time());

		post_message_with_param("MSG_STORAGE_SAVE", $GLOBALS["objects"][$t_oid]->class_id(), array(
			"oid" => $t_oid
		));

		return $t_oid;
	}

	// returns true/false based on whether the parameter is the object class member function
	function is_object_member_fun($func)
	{
		return in_array($func, $this->object_member_funcs);
	}

	// load properties - arr[file] , arr[clid]
	function load_properties($arr)
	{
		if ($arr["file"] === "document" || $arr["file"] === "document_brother")
		{
			$arr["file"] = "doc";
		}

		// if system is set, then no captions/translations/etc will be loaded,
		// since storage really doesn't care. so why should property loader?

		$arr["system"] = 1;

		// cfgu->load_properties is expensive, so we cache the results.
		// why here? because it does a lot more than just load properties
		// and it's a bit tricky to cache all that information there --duke
		//
		// removed the caching from here, it is done in object::_int_load_properties
		// it won't call this function if an object of the same class id has been loaded before
		// so doing the caching here is just wasting memory now.
		// - terryf
		$cls = aw_ini_get("classes");
		if (!isset($cls[$arr["clid"]]) && empty($arr["file"]))
		{
			return array(array(), array(), array(), array(), array());
		}
		$props = $this->cfgu->load_properties($arr);
		$rv = array($props, $this->cfgu->tableinfo, $this->cfgu->relinfo, $this->cfgu->classinfo, $this->cfgu->groupinfo);
		return $rv;
	}

	/** switches the object_loader's default database connection to $new_conn

		@attrib params=pos

		@param new_conn required

		new conn is the new database connection to set the datasource to
		returns the old connection
	**/
	function switch_db_connection($new_conn)
	{
		// ok, we need to find the real connection.
		// iterate over the ds chain until we hit the last one.
		// that should be the final database ds
		$ds =& $this->ds;
		while (is_object($ds->contained))
		{
			$ds =& $ds->contained;
		}

		if (!is_object($ds))
		{
			error::raise(array(
				"id" => "ERR_NO_DS",
				"msg" => sprintf(t("object_loader::switch_db_connection(%s): could nto find root connection!"), $new_conn)
			));
			return;
		}

		$old = $ds->dc[$ds->default_cid];
		$ds->dc[$ds->default_cid] = $new_conn;
		$this->dc[$ds->default_cid] = $new_conn;
		return $old;
	}

	function can($acl_name, $oid, $dbg = false)
	{
		$acl_name = "can_" . $acl_name;

		if (!in_array($acl_name, $this->acl_ids))
		{
			return 0;
		}

		if (!isset($this->__aw_acl_cache[$oid]) || !($max_acl = $this->__aw_acl_cache[$oid]))
		{
			$fn = "acl-".$oid."-uid-".(isset($_SESSION["uid"]) ? $_SESSION["uid"] : "");
			$fn .= "-nliug-".(isset($_SESSION["nliug"]) ? $_SESSION["nliug"] : "");
			if (empty($GLOBALS["__obj_sys_opts"]["no_cache"]) && ($str_max_acl = $this->cache->file_get_pt_oid("acl", $oid, $fn)) != false)
			{
				$max_acl = aw_unserialize($str_max_acl, false, true);
			}

			if (!isset($max_acl))
			{
				$max_acl = $this->_calc_max_acl($oid);
				if ($max_acl === false)
				{
					$max_acl = array_combine($this->acl_ids, array_fill(0, count($this->acl_ids), false));
				}

				if (empty($GLOBALS["__obj_sys_opts"]["no_cache"]))
				{
					$this->cache->file_set_pt_oid("acl", $oid, $fn, aw_serialize($max_acl, SERIALIZE_NATIVE));
				}
			}

			$this->__aw_acl_cache[$oid] = $max_acl;
		}

		if (!isset($max_acl["can_view"]) && empty($_SESSION["uid"]))
		{
			return $GLOBALS["cfg"]["acl"]["default"]; //!!! acl.default setting on array! default on 1 view-l. teistel ini-s m22ramata. parandada!
		}

		return (int) isset($max_acl[$acl_name]) ? $max_acl[$acl_name] : 0;
	}

	function _calc_max_acl($oid)
	{
		$max_priority = -1;
		$max_acl = $GLOBALS["cfg"]["acl"]["default"];

		$gl = aw_global_get("gidlist_pri_oid");
		// go through the object tree and find the acl that is of highest priority among the current users group
		$cur_oid = $oid;
		$do_orig = false;
		$cnt = 0;
		while ($cur_oid > 0)
		{
			$tmp = null;
			if (isset($GLOBALS["__obj_sys_acl_memc"][$cur_oid]) && isset($GLOBALS["__obj_sys_acl_memc"][$cur_oid]["acldata"]))
			{
				$tmp = $GLOBALS["__obj_sys_acl_memc"][$cur_oid];
			}
			elseif (isset($GLOBALS["__obj_sys_objd_memc"][$cur_oid]) && isset($GLOBALS["__obj_sys_objd_memc"][$cur_oid]["acldata"]))
			{
				$tmp = $GLOBALS["__obj_sys_objd_memc"][$cur_oid];
			}
			elseif (isset($GLOBALS["objects"][$cur_oid]))
			{
				$tmp = $GLOBALS["objects"][$cur_oid]->get_object_data();
			}
			else
			{
				$tmp = $this->ds->get_objdata($cur_oid, array(
					"no_errors" => true
				));
				if ($tmp !== NULL)
				{
					$GLOBALS["__obj_sys_objd_memc"][$cur_oid] = $tmp;
				}
			}

			if ($tmp === NULL)
			{
				// if any object above the one asked for is deleted, no access
				return false;
			}

			// status and brother_of are not set when acl data is read from e.g. acl mem cache
			if (isset($tmp["status"]))
			{
				if ($tmp["status"] == 0)
				{
					return false;
				}
			}

			if (isset($tmp["brother_of"]) && $cur_oid != $tmp["brother_of"] && $tmp["brother_of"] > 0 && $cur_oid == $oid)
			{
				$do_orig = $tmp["brother_of"];
			}

			$acld = safe_array($tmp["acldata"]);

			// now, iterate over the current acl data with the current gidlist
			// and find the highest priority acl currently
			foreach($acld as $g_oid => $g_acld)
			{
				// this applies the affects subobjects setting - if the first object has this set, then ignore the acls for that
				$skip = false; //$cur_oid == $oid && $g_acld["can_subs"] == 1;

				if (isset($gl[$g_oid]) && $gl[$g_oid] > $max_priority && !$skip)
				{
					$max_acl = $g_acld;
					$max_priority = $gl[$g_oid];
				}
			}

			if (++$cnt > 100)//!!! move this limit setting to config?
			{
				$this->raise_error("ERR_ACL_EHIER", sprintf(t("object_loader->can(%s, %s): error in object hierarchy, count exceeded!"), $access,$oid),true);
			}

			// go to parent
			$cur_oid = $tmp["parent"];
		}

		// now, we have the result. but for brothers we need to do this again for the original object and only use the can_delete privilege from the brother
		if ($do_orig !== false)
		{
			$rv = $this->_calc_max_acl($do_orig);
			if ($rv === false)
			{
				return false;   // if the original is deleted, then the brother is deleted as well
			}
			$rv["can_delete"] = $max_acl["can_delete"];
			return $rv;
		}
		return $max_acl;
	}

	function _log($new, $oid, $name, $clid = NULL)
	{
		if ($clid === NULL)
		{
			$tmpo = obj($oid);
			// get object's class info
			$clid = $tmpo->class_id();
		}

		if ($clid == 7)
		{
			$type = "ST_DOCUMENT";
		}
		elseif (!empty($GLOBALS["classinfo"][$clid]["syslog_type"]["text"]))
		{
			$type = $GLOBALS["classinfo"][$clid]["syslog_type"]["text"];
		}
		else
		{
			$type = 10000;
		}

		$this->cache->_log($type, ($new ? "SA_ADD" : "SA_CHANGE"), $name, $oid, false, $name);
	}

	function resolve_reltype($type, $class_id)
	{
		if (is_array($type))
		{
			$res = array();
			foreach($type as $ot)
			{
				if (!is_numeric($ot) && substr($ot, 0, 7) === "RELTYPE")
				{
					$res[] = $this->_resolve_single_rt($ot, $class_id);
				}
				else
				{
					$res[] = $ot;
				}
			}
			return $res;
		}
		else
		if (!is_numeric($type) && substr($type, 0, 7) == "RELTYPE")
		{
			return $this->_resolve_single_rt($type, $class_id);
		}
		return $type;
	}

	function _resolve_single_rt($type, $class_id)
	{
		// it is "RELTYPE_FOO"
		// resolve it to numeric
		if (empty($GLOBALS["relinfo"][$class_id][$type]["value"]))
		{
			return -1; // won't match anything
		}
		return $GLOBALS["relinfo"][$class_id][$type]["value"];
	}

	function handle_cache_update($oid, $site_id, $type)
	{
		if (!$this->registered)
		{
			register_shutdown_function(array(&$this, "on_shutdown_update_cache"));
			$this->cache_handlers = array();
			$this->registered = true;
		}

		try
		{
			$repl = aw_ini_get("object_cache.replicate_sites");
		}
		catch (Exception $e)
		{
			$repl = null;
		}

		if (is_array($repl) || $site_id != aw_ini_get("site_id"))
		{
			$this->cache_handlers[$site_id][$oid][$type] = $type;
		}
	}

	function on_shutdown_update_cache()
	{
		// go over all the registered cache updates and if they are for another site, then propagate them to that one
		$sl = get_instance("install/site_list");
		$f = fopen(aw_ini_get("site_basedir")."/files/updlog.txt", "a");

		try // shutdown functions can't throw exceptions
		{
			$repl = aw_ini_get("object_cache.replicate_sites");
			$cur_sid = aw_ini_get("site_id");
		}
		catch (Exception $e)
		{
			$repl = null;
			$cur_sid = null;
		}

		foreach($this->cache_handlers as $site_id => $data)
		{
			if (is_array($repl))
			{
				foreach($repl as $url)
				{
					$this->_do_repl_call($url, $data, $f);
				}
				continue;
			}
			else
			if ($site_id == $cur_sid)
			{
				continue;
			}
			else
			{
				$url = $sl->get_url_for_site($site_id);
			}
			$this->_do_repl_call($url, $data, $f);
		}
		fclose($f);
	}

	function _do_repl_call($url, $data, $f)
	{
		fwrite($f, "call $site_id => ".dbg::dump($data)." from site ".$url."\n\n");
		fflush($f);
		if ($url != "")
		{
			aw_global_set("__from_raise_error", 1);
			$this->do_orb_method_call(array(
				"server" => $url,
				"method" => "xmlrpc",
				"class" => "object_cache_updater",
				"action" => "handle_remote_update",
				"params" => array(
					"data" => $data
				),
				"no_errors" => 1	// sites may not respond or be password protected or whatever and the user does not need to see that
			));
			aw_global_set("__from_raise_error", 0);
		}
	}

	function handle_no_cache_clear()
	{
		$this->cache->file_clear_pt("html");
		$this->cache->file_clear_pt("acl");
		$this->cache->file_clear_pt("menu_area_cache");
		$this->cache->file_clear_pt("storage_search");
		$this->cache->file_clear_pt("storage_object_data");
	}

	public function set___aw_acl_cache($oid = NULL, $v = array())
	{
		if(isset($oid))
		{
			$this->__aw_acl_cache[$oid] = $v;
		}
		else
		{
			$this->__aw_acl_cache = $v;
		}
	}
}

$GLOBALS["objects"] = array();
$GLOBALS["properties"] = array();
$GLOBALS["tableinfo"] = array();
$GLOBALS["of2prop"] = array();
$GLOBALS["__obj_sys_opts"] = array();

?>
