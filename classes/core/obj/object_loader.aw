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
			// reloacate the object in the global list
			$GLOBALS["objects"][$t_oid] = $GLOBALS["objects"][$oid];
			unset($GLOBALS["objects"][$oid]);
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

		// write the current time as last modification time of any object.
		$this->cache->file_set("objlastmod", time());

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
		$rv = array($props, $this->cfgu->tableinfo);
		return $rv;
	}

	function object_exists($oid)
	{
		return $this->ds->object_exists($this->param_to_oid($oid));
	}
}

$GLOBALS["object_loader"] = new _int_object_loader();
$GLOBALS["objects"] = array();

?>
