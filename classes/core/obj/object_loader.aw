<?php

/*

this message will get called whenever an object is saved as the class_id as the message type parameter
and the object's id as the "oid" parameter
EMIT_MESSAGE(MSG_STORAGE_SAVE)

*/

class _int_object_loader
{
	// private variables, only object system classes can use these
	var $ds; 					// data source
	var $object_member_funcs;	// names of all object class member functions
	var $cfgu;					// cfgutilities instance
	var $cache;					// cache class instance

	function _int_object_loader()
	{
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
	}

	function oid_for_alias($alias)
	{
		// there is no quote here -- duke
		//$this->quote($alias);

		if (substr($alias,-1) == "/")
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
				"alias" => $part,
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
				error::throw(array(
					"id" => ERR_NO_ALIAS,
					"msg" => "object_loader::param_to_oid($param): no object with alias $param!"
				));
			}
			return $oid;
		}
		else
		if (is_object($param))
		{
			$cl = get_class($param);
			if ($cl == "object" || $cl == "_int_object")
			{
				return $param->id();
			}
		}

		error::throw(array(
			"id" => ERR_PARAM,
			"msg" => "object_loader::param_to_oid(): parameter must be either: oid , string (alias) or object instance!"
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
				error::throw(array(
					"id" => ERR_NO_ALIAS,
					"msg" => "no object with alias $param!"
				));
			}
			return array($oid);
		}
		else
		if (is_object($param))
		{
			$cl = get_class($param);
			if ($cl == "object" || $cl == "_int_object")
			{
				return array($param->id());
			}
			else
			if ($cl == "object_list")
			{
				return $param->ids();
			}
			else
			if ($cl == "object_tree")
			{
				return $param->ids();
			}
		}
		else
		if (is_array($param))
		{
			return $param;
		}

		error::throw(array(
			"id" => ERR_PARAM,
			"msg" => "parameter must be either: oid , string (alias), object instance or object list instance!"
		));
	}


	////
	// !returns temp id for new object
	function new_object_temp_id($param = NULL)
	{
		$cnt = 0;
		$str = "new_object_temp_id_".$cnt;
		while (isset($GLOBALS["objects"][$str]))
		{
			$cnt++;
			$str = "new_object_temp_id_".$cnt;
		}
		$GLOBALS["objects"][$str] =& new _int_object($param);
		return $str;
	}

	function load($oid)
	{
		if (!is_oid($oid))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_loader::load($oid): parameter is not object id!"
			));
		}

		if (!is_object($GLOBALS["objects"][$oid]))
		{
			$ref = &new _int_object($oid);
			if ($ref->id() === NULL)
			{
				error::throw(array(
					"id" => ERR_PARAM,
					"msg" => "object_loader::load($oid): no such object!"
				));
			}
			else
			{
				$GLOBALS["objects"][$oid] = $ref;
			};
		}

		// also remove the entry from acl memc cause we dont need it no more
		if (isset($GLOBALS["__obj_sys_acl_memc"][$oid]))
		{
			unset($GLOBALS["__obj_sys_acl_memc"][$oid]);
		}

		return $GLOBALS["objects"][$oid]->id();
	}

	function save($oid)
	{
		if (!is_object($GLOBALS["objects"][$oid]))
		{
			error::throw(array(
				"id" => ERR_OBJECT,
				"msg" => "object_loader::save($oid): no object with oid $oid exists in the global list"
			));
		}

		$t_oid = $GLOBALS["objects"][$oid]->save();
		if ($t_oid != $oid)
		{
			// relocate the object in the global list
			$GLOBALS["objects"][$t_oid] = $GLOBALS["objects"][$oid];
			$GLOBALS["objects"][$oid] =& $GLOBALS["objects"][$t_oid];
		}
		// return the new value, so that the pointers go to the right place.
		//
		// the problem that there might be other pointers to the previous object should not arise, 
		// because you probably will not be able to acquire pointers to temp-objects.
		// probably.
		// well, here's to hoping it won't happen!

		post_message_with_param(MSG_STORAGE_SAVE, $GLOBALS["objects"][$t_oid]->class_id(), array(
			"oid" => $t_oid
		));

		if (aw_ini_get("site_show.objlastmod_only_menu"))
		{
			if ($GLOBALS["objects"][$t_oid]->class_id() == CL_MENU)
			{
				// write the current time as last modification time of any object.
				$this->cache->file_set("objlastmod", time());
				$this->cache->file_invalidate("menuedit-menu_cache-lang-".aw_global_get("lang_id")."-site_id-".aw_ini_get("site_id")."-period-".aw_global_get("act_per_id"));
			}
		}
		else
		{
			// write the current time as last modification time of any object.
			$this->cache->file_set("objlastmod", time());
			$this->cache->file_invalidate("menuedit-menu_cache-lang-".aw_global_get("lang_id")."-site_id-".aw_ini_get("site_id")."-period-".aw_global_get("act_per_id"));
		}

		return $t_oid;
	}

	function save_new($oid)
	{
		if (!is_object($GLOBALS["objects"][$oid]))
		{
			error::throw(array(
				"id" => ERR_OBJECT,
				"msg" => "object_loader::save_new($oid): no object with oid $oid exists in the global list"
			));
		}

		// right. here we need to make a copy BEFORE calling save_new, because
		// otherwise the previous object will get it's oid ovewritten
		$t_o = $GLOBALS["objects"][$oid];
		$t_oid = $t_o->save_new();

		// copy the object to the new place
		$GLOBALS["objects"][$t_oid] = $t_o;

		$this->cache->file_set("objlastmod", time());
		$this->cache->file_invalidate("menuedit-menu_cache-lang-".aw_global_get("lang_id")."-site_id-".aw_ini_get("site_id")."-period-".aw_global_get("act_per_id"));

		post_message_with_param(MSG_STORAGE_SAVE, $GLOBALS["objects"][$t_oid]->class_id(), array(
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
		if ($arr["file"] == "document" || $arr["file"] == "document_brother")
		{
			$arr["file"] = "doc";
		}
		// cfgu->load_properties is expensive, so we cache the results.
		// why here? because it does a lot more than just load properties
		// and it's a bit tricky to cache all that information there --duke
		// 
		// removed the caching from here, it is done in object::_int_load_properties
		// it won't call this function if an object of the same class id has been loaded before
		// so doing the caching here is just wasting memory now. 
		// - terryf
		$props = $this->cfgu->load_properties($arr);
		$rv = array($props, $this->cfgu->tableinfo, $this->cfgu->relinfo);
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

		error::throw_if(!is_object($ds), array(
			"id" => ERR_NO_DS,
			"msg" => "object_loader::switch_db_connection($new_conn): could nto find root connection!"
		));

		$old = $ds->dc[$ds->default_cid];
		$ds->dc[$ds->default_cid] = $new_conn;
		return $old; 
	}

	function can($acl_name, $oid, $dbg = false)
	{
		// we get the acl from read_objdata
		enter_function("object_loader::can");
		if (!($max_acl = aw_cache_get("__aw_acl_cache", $oid)))
		{
			$max_priority = -1;
			$max_acl = $GLOBALS["cfg"]["acl"]["default"];

			$gl = aw_global_get("gidlist_pri_oid");


			// go through the object tree and find the acl that is of highest priority among the current users group
			$cur_oid = $oid;
			while ($cur_oid > 0)
			{
				if (isset($GLOBALS["__obj_sys_acl_memc"][$cur_oid]))
				{
					$tmp = $GLOBALS["__obj_sys_acl_memc"][$cur_oid];
				}
				else
				if (isset($GLOBALS["__obj_sys_objd_memc"][$cur_oid]))
				{
					$tmp = $GLOBALS["__obj_sys_objd_memc"][$cur_oid];
				}
				else
				if ($GLOBALS["objects"][$cur_oid])
				{
					$tmp = $GLOBALS["objects"][$cur_oid]->obj;
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
					exit_function("object_loader::can");
					return false;
				}

				$acld = safe_array($tmp["acldata"]);

				// now, iterate over the current acl data with the current gidlist
				// and find the highest priority acl currently
				foreach($gl as $g_oid => $g_pri)
				{
					if ($g_pri > $max_priority && isset($acld[$g_oid]))
					{
						$max_acl = $acld[$g_oid];
						$max_priority = $g_pri;
					}
				}

				if (++$cnt > 100)
				{
					$this->raise_error(ERR_ACL_EHIER,"object_loader->can($access,$oid): error in object hierarchy, count exceeded!",true);
				}

				// go to parent
				$cur_oid = $tmp["parent"];
			}

			aw_cache_set("__aw_acl_cache", $oid, $max_acl);
		}

		exit_function("object_loader::can");
		if (!isset($max_acl["can_view"]) && aw_global_get("uid") == "")
		{
			return $GLOBALS["cfg"]["acl"]["default"];
		}

		//echo "------- return ".((int)$max_acl["can_".$acl_name])." for $acl_name on object $oid <br>\n";
		// and now return the highest found
		return (int)$max_acl["can_".$acl_name];
	}
}

$GLOBALS["object_loader"] = new _int_object_loader();
$GLOBALS["objects"] = array();
?>
