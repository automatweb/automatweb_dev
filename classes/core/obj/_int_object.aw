<?php 

/*

this message will get called whenever an object is deleted and the class_id as the message type parameter
and the object's id as the "oid" parameter
EMIT_MESSAGE(MSG_STORAGE_DELETE)

*/


class _int_object 
{
	///////////////////////////////////////////
	// private variables

	var $obj;			// actual object data
	var $implicit_save; 
	

	///////////////////////////////////////////
	// public functions

	function _int_object($param = NULL)
	{
		if ($param != NULL)
		{
			$this->load($param);
		}
		else
		{
			$this->_init_empty();
		}
	}

	function load($param)
	{
		if (is_array($param))
		{
			$this->obj = $param;
		}
		else
		{
			$this->_int_load($GLOBALS["object_loader"]->param_to_oid($param));
		}
	}

	function save()
	{
		if (!$this->_int_can_save())
		{
			error::throw(array(
				"id" => ERR_SAVE,
				"msg" => "object::save(): object cannot be saved, needed properties are not set (parent, class_id)"
			));
		}

		return $this->_int_do_save();
	}

	function save_new()
	{
		$this->_int_set_of_value("oid", 0);
		$this->_int_set_of_value("brother_of", 0);
		return $this->save();
	}

	function set_implicit_save($param)
	{
		$prev = $this->implicit_save;
		$this->implicit_save = $param;
		return $prev;
	}

	function get_implicit_save()
	{
		return ($this->implicit_save ? true : false);
	}

	function arr()
	{
		return $this->obj;
	}

	function delete()
	{
		if (!$this->obj["oid"])
		{
			error::throw(array(
				"id" => ERR_NO_OID,
				"msg" => "object::delete(): no current object loaded"
			));
		}
		
		if (!$this->can("delete"))
		{
			error::throw(array(
				"id" => ERR_ACL,
				"msg" => "object::delete(): no delete access for current object (".$this->obj["oid"].")"
			));
		}

		$this->_int_do_delete($this->obj["oid"]);
	}

	function connect($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object::connect($param): parameter must be an array of connection parameters!"
			));
		}

		$oids = $GLOBALS["object_loader"]->param_to_oid_list($param["to"]);
		foreach($oids as $oid)
		{
			// check if a connection to the same object with the same reltype already exists
			// if it does, then don't do it again.
			$cprms = array("to" => $oid);
			if ($param["reltype"])
			{
				$cprms["type"] = $param["reltype"];
			}

			if (count($this->connections_from($cprms)) > 0)
			{
				continue;
			}

			$c = new connection();
			$param["from"] = $this->obj["oid"];
			$param["to"] = $oid;
			$c->change($param);
		}
	}

	function disconnect($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object::disconnect($param): parameter must be an array!"
			));
		}

		$oids = $GLOBALS["object_loader"]->param_to_oid_list($param["from"]);
		foreach($oids as $oid)
		{
			$c = new connection();
			$conn_id = $c->find(array(
				"from" => $this->obj["oid"],
				"to" => $oid,
			));
			if (count($conn_id) < 1)
			{
				error::throw(array(
					"id" => ERR_CONNECTION,
					"msg" => "object::disconnect(): could not find connection to object $oid from object ".$this->obj["oid"]
				));
			}
			reset($conn_id);
			list(, $f_c) = each($conn_id);
			$c->load($f_c["id"]);
			$c->delete();
		}
	}

	function connections_from($param = NULL)
	{
		if (!$this->obj["oid"])
		{
			error::throw(array(
				"id" => ERR_NO_OBJ,
				"msg" => "object::connections_from(): no current object loaded!"
			));
		}

		$filter = array(
			"from" => $this->obj["oid"]
		);

		if ($param != NULL)
		{
			if (!is_array($param))
			{
				error::throw(array(
					"id" => ERR_PARAM,
					"msg" => "object::connections_from(): if argument is present, then argument must be array of filter parameters!"
				));
			}
			if (isset($param["type"]))
			{
				$filter["type"] = $param["type"];
			}
			if (isset($param["class"]))
			{
				$filter["class"] = $param["class"];
			}
			if (isset($param["to"]))
			{
				$filter["to"] = $param["to"];
			}
			foreach($param as $k => $v)
			{
				if (substr($k, 0, 3) == "to.")
				{
					$filter[$k] = $v;
				}
				if (substr($k, 0, 5) == "from.")
				{
					$filter[$k] = $v;
				}
			}
		}

		$ret = array();
		$cs = $GLOBALS["object_loader"]->ds->find_connections($filter);
		foreach($cs as $c_id => $c_d)
		{
			if ($this->can("view", $c_d["from"]) && 
				$this->can("view", $c_d["to"]) && 
				$GLOBALS["object_loader"]->object_exists($c_d["from"]) && 
				$GLOBALS["object_loader"]->object_exists($c_d["to"])
			)
			{
				$ret[$c_id] =& new connection($c_d);
			}
		}

		if ($param["sort_by"] != "")
		{
			usort($ret, create_function('$a,$b', 'return strcasecmp($a->prop("'.$param["sort_by"].'"), $b->prop("'.$param["sort_by"].'"));'));
		}

		return $ret;
	}

	function connections_to($param = NULL)
	{
		if (!$this->obj["oid"])
		{
			error::throw(array(
				"id" => ERR_NO_OBJ,
				"msg" => "object::connections_to(): no current object loaded!"
			));
		}

		$filter = array(
			"to" => $this->obj["oid"]
		);

		if ($param != NULL)
		{
			if (!is_array($param))
			{
				error::throw(array(
					"id" => ERR_PARAM,
					"msg" => "object::connections_to(): if argument is present, then argument must be array of filter parameters!"
				));
			}
			if (isset($param["type"]))
			{
				$filter["type"] = $param["type"];
			}
			if (isset($param["class"]))
			{
				$filter["class"] = $param["class"];
			}
			if (isset($param["from"]))
			{
				$filter["from"] = $param["from"];
			}
			if (isset($param["sort_by"]))
			{
				$filter["sort_by"] = $param["sort_by"];
			}
			foreach($param as $k => $v)
			{
				if (substr($k, 0, 3) == "to.")
				{
					$filter[$k] = $v;
				}
				if (substr($k, 0, 5) == "from.")
				{
					$filter[$k] = $v;
				}
			}
		}

		$ret = array();
		$cs = $GLOBALS["object_loader"]->ds->find_connections($filter);
		foreach($cs as $c_d)
		{
			if ($this->can("view", $c_d["from"]) && 
				$this->can("view", $c_d["to"]) &&
				$GLOBALS["object_loader"]->object_exists($c_d["from"]) && 
				$GLOBALS["object_loader"]->object_exists($c_d["to"])
			)
			{
				$ret[] =& new connection($c_d);
			}
		}

		if ($param["sort_by"] != "")
		{
			usort($ret, create_function('$a,$b', 'return strcasecmp($a->prop("'.$param["sort_by"].'"), $b->prop("'.$param["sort_by"].'"));'));
		}

		return $ret;
	}

	function path($param = NULL)
	{
		if (!is_object($this))
		{
			$o =& new object($param);
			return $o->path();
		}

		if (!$this->obj["oid"])
		{
			error::throw(array(
				"id" => ERR_NO_OBJ,
				"msg" => "object::path(): no object loaded!"
			));
		}

		return $this->_int_path();
	}

	function path_str($param = NULL)
	{
		if (!param != NULL)
		{
			if (!is_array($param))
			{
				error::throw(array(
					"id" => ERR_PARAM,
					"msg" => "object::path_str(): parameter must be an array if present!"
				));
			}
		}

		$pt = $this->path();
		$i = 0;
		$cnt = count($pt);
        	if (isset($param["max_len"]))
		{
			$i = $cnt - $param["max_len"];
		}

		$ret = array();
		for(; $i < $cnt; $i++)
		{
			if (is_object($pt[$i]))
			{
				$ret[] = $pt[$i]->name();
			}
		}
		return join(" / ", $ret);
	}

	function can($param)
	{
		if (!$this->obj["oid"])
		{
			error::throw(array(
				"id" => ERR_ACL,
				"msg" => "object::can($param): no current object loaded!"
			));
		}

		return $this->_int_can($param);
	}

	function is_property($param)
	{
		return $this->_int_is_property($param);
	}

	function parent()
	{
		return $this->obj["parent"];
	}

	function set_parent($parent)
	{
		$prev = $this->obj["parent"];

		$parent = $GLOBALS["object_loader"]->param_to_oid($parent);

		if (!$parent)
		{
			error::throw(array(
				"id" => ERR_NO_PARENT,
				"msg" => "object::set_parent($parent): no parent specified!"
			));
		}

		$this->_int_set_of_value("parent", $parent);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function name()
	{
		return $this->obj["name"];
	}

	function set_name($param)
	{
		$prev = $this->obj["name"];

		$this->_int_set_of_value("name", $param);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function class_id()
	{
		return $this->obj["class_id"];
	}

	function set_class_id($param)
	{
		$prev = $this->obj["class_id"];

		if (!is_class_id($param))
		{
			error::throw(array(
				"id" => ERR_CLASS_ID,
				"msg" => "object::set_class($param): specified class id id is not valid!"
			));
		}

		$this->_int_set_of_value("class_id", $param);
		// since the class id has changed, we gots to load new properties for the new class type
		$this->_int_load_properties();
		$this->_int_do_implicit_save();
		return $prev;
	}

	function status()
	{
		return $this->obj["status"];
	}

	function set_status($param)
	{
		$prev = $this->obj["status"];

		switch($param)
		{
			case STAT_DELETED:
				$this->_int_set_of_value("status", STAT_DELETED);
				return $this->delete();
				break;

			case STAT_ACTIVE: 
				$this->_int_set_of_value("status", STAT_ACTIVE);
				break;

			case STAT_NOTACTIVE: 
				$this->_int_set_of_value("status", STAT_NOTACTIVE);
				break;

			default:
				error::throw(array(
					"id" => ERR_STATUS,
					"msg" => "object::set_status($param): incorrect status code!"
				));
		}

		$this->_int_do_implicit_save();
		return $prev;
	}

	function lang()
	{
		if (!isset($this->obj["lang_id"]))
		{
			return NULL;
		}

		// right. once we convert all code to use lang codes (en/et/..) we can make this better. right now, the sucky version.
		$li = get_instance("languages");
		return $li->get_langid($this->obj["lang_id"]);
	}

	function lang_id()
	{
		return $this->obj["lang_id"];
	}

	function set_lang($param)
	{
		$prev = $this->lang();

		$li = get_instance("languages");
		$lang_id = $li->get_langid_for_code($param);
		if (!$lang_id)
		{
			error::throw(array(
				"id" => ERR_LANG,
				"msg" => "object::set_lang($param): no language with such code is defined in the system!"
			));
		}

		$this->_int_set_of_value("lang_id", $lang_id);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function comment()
	{
		return $this->obj["comment"];
	}

	function set_comment($param)
	{
		$prev = $this->obj["comment"];
		$this->_int_set_of_value("comment", $param);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function ord()
	{
		return $this->obj["jrk"];
	}

	function set_ord($param)
	{
		$prev = $this->obj["jrk"];

		if (!is_numeric($param))
		{
			error::throw(array(
				"id" => ERR_ORD,
				"msg" => "object::set_ord($param): order must be integer!"
			));
		}

		$this->_int_set_of_value("jrk", (int)$param);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function alias()
	{
		return $this->obj["alias"];
	}

	function set_alias($param)
	{
		$prev = $this->obj["alias"];

		$this->_int_set_of_value("alias", $param);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function id()
	{
		return $this->obj["oid"];
	}

	function createdby()
	{
		// mkay. l8r, when we start saving the user oid in this field, this will be lots faster and better. 
		// right now, the sucky version:
		$uid = $this->obj["createdby"];
		if (!$uid)
		{
			return NULL;
		}

		$ui = get_instance("users");
		$oid = $ui->get_oid_for_uid($uid);
		if (!$oid)
		{
			error::throw(array(
				"id" => ERR_USER_NO_OID,
				"msg" => "object::createdby(): the user $uid, who created the current object (".$this->obj["oid"]."), has no object!"
			));
		}
		return new object($oid);
	}

	function created()
	{
		return $this->obj["created"];
	}

	function modifiedby()
	{
		// mkay. l8r, when we start saving the user oid in this field, this will be lots faster and better. 
		// right now, the sucky version:
		$uid = $this->obj["modifiedby"];
		if (!$uid)
		{
			return NULL;
		}

		$ui = get_instance("users");
		$oid = $ui->get_oid_for_uid($uid);
		if (!$oid)
		{
			error::throw(array(
				"id" => ERR_USER_NO_OID,
				"msg" => "object::createdby(): the user $uid, who last modified the current object (".$this->obj["oid"]."), has no object!"
			));
		}
		return new object($oid);
	}

	function modified()
	{
		return $this->obj["modified"];
	}

	function period()
	{
		return $this->obj["period"];
	}

	function set_period($param)
	{
		$prev = $this->obj["period"];

		if (!is_numeric($param))
		{
			error::throw(array(
				"id" => ERR_PERIOD,
				"msg" => "object::set_period($param): period must be integer!"
			));
		}

		$this->_int_set_of_value("period", (int)$param);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function is_periodic()
	{
		return $this->obj["periodic"];
	}

	function set_periodic($param)
	{
		$prev = $this->obj["periodic"];

		if (!(is_numeric($param) || is_bool($param)))
		{
			error::throw(array(
				"id" => ERR_BOOL,
				"msg" => "object::set_periodic($param): order must be integer or boolean!"
			));
		}

		$this->_int_set_of_value("periodic", ($param ? true : false));
		$this->_int_do_implicit_save();
		return $prev;
	}

	function site_id()
	{
		return $this->obj["site_id"];
	}

	function set_site_id($param)
	{
		$prev = $this->obj["site_id"];

		if (!is_numeric($param))
		{
			error::throw(array(
				"id" => ERR_SITE_ID,
				"msg" => "object::set_site_id($param): site_id must be integer!"
			));
		}

		$this->_int_set_of_value("site_id", (int)$param);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function is_brother()
	{
		if (!$this->obj["oid"])
		{
			return NULL;
		}

		return ($this->obj["oid"] != $this->obj["brother_of"]);
	}

	function get_original()
	{
		if ($this->is_brother())
		{
			return new object($this->obj["brother_of"]);
		}

		return $this;
	}

	function subclass()
	{
		return $this->obj["subclass"];
	}

	function set_subclass($param)
	{
		$prev = $this->obj["subclass"];

		if (!is_numeric($param))
		{
			error::throw(array(
				"id" => ERR_SUBCLASS,
				"msg" => "object::set_subclass($param): subclass must be integer!"
			));
		}

		$this->_int_set_of_value("subclass", (int)$param);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function flags()
	{
		return $this->obj["flags"];
	}

	function set_flags($param)
	{
		$prev = $this->obj["flags"];

		if (!is_numeric($param))
		{
			error::throw(array(
				"id" => ERR_FLAGS,
				"msg" => "object::set_flags($param): flags must be integer!"
			));
		}

		$this->_int_set_of_value("flags", (int)$param);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function flag($param)
	{
		if (!is_numeric($param))
		{
			error::throw(array(
				"id" => ERR_FLAG,
				"msg" => "object::flag($param): flag must be integer!"
			));
		}

		return $this->obj["flags"] & $param;
	}

	function set_flag($flag, $val)
	{
		if (!is_numeric($flag))
		{
			error::throw(array(
				"id" => ERR_FLAG,
				"msg" => "object::set_flag($flag, $val): flag must be integer!"
			));
		}
		if (!(is_numeric($val) || is_bool($val)))
		{
			error::throw(array(
				"id" => ERR_FLAG,
				"msg" => "object::set_flag($flag, $val): value must be integer!"
			));
		}

		$prev = $this->flag($flag);

		if ($val)
		{
			// if set flag, then or the current bits with the value
			$value = $this->obj["flags"] | $flag;
		}
		else
		{
			$mask = OBJ_FLAGS_ALL ^ $flag;
			$value = $this->obj["flags"] & $mask;
		}

		$this->_int_set_of_value("flags", $value);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function meta($param = false)
	{
		// calling this withoun an argument returns the contents of whole metainfo
		// site_content->build_menu_chain for example needs access to the whole metainfo at once -- duke
		if ($param === false)
		{
			$retval = $this->obj["meta"];
		}
		else
		{
			$retval = $this->obj["meta"][$param];
		};
		return $retval;
	}

	function set_meta($key, $value)
	{
		$prev = $this->obj["meta"][$key];

		$this->obj["meta"][$key] = $value;

		// if any property is defined for metadata, we gots to sync from object to property
		if (is_array($GLOBALS["properties"][$this->obj["class_id"]][$key]) && $GLOBALS["properties"][$this->obj["class_id"]][$key]["field"] == "meta" && $GLOBALS["properties"][$this->obj["class_id"]][$key]["table"] == "objects")
		{
			$this->obj["properties"][$key] = $value;
		}

		$this->_int_do_implicit_save();
		return $prev;
	}

	function get_property_list()
	{
		return $GLOBALS["properties"][$this->obj["class_id"]];
	}

	function prop($param)
	{
		$retval = $this->obj["properties"][$param];
		return $retval;
	}

	function set_prop($key, $val)
	{
		if (!$this->_int_is_property($key))
		{
			error::throw(array(
				"id" => ERR_PROP,
				"msg" => "object::set_prop($key, $val): no property $key defined for current object!"
			));
		}

		$prev = $this->obj["properties"][$key];

		$this->obj["properties"][$key] = $val;

		// if this is an object field property, sync to object field
		if ($GLOBALS["properties"][$this->obj["class_id"]][$key]["table"] == "objects")
		{
			if ($GLOBALS["properties"][$this->obj["class_id"]][$key]["field"] == "meta")
			{
				$this->obj["meta"][$GLOBALS["properties"][$this->obj["class_id"]][$key]["name"]] = $val;
			}
			if ($GLOBALS["properties"][$this->obj["class_id"]][$key]["method"] == "bitmask")
			{
				// it's flags, sync to that
				$mask = $this->obj["flags"];
				// zero out cur field bits
				$mask = $mask & (~((int)$GLOBALS["properties"][$this->obj["class_id"]][$key]["ch_value"]));
				$mask = $mask | $val;
				$this->obj["flags"] = $mask;
			}
			else
			{
				if ($GLOBALS["properties"][$this->obj["class_id"]][$key]["method"] == "serialize")
				{
					$this->obj[$GLOBALS["properties"][$this->obj["class_id"]][$key]["field"]][$GLOBALS["properties"][$this->obj["class_id"]][$key]["name"]] = $this->obj["properties"][$key];
				}
				else
				{
					$this->obj[$GLOBALS["properties"][$this->obj["class_id"]][$key]["field"]] = $this->obj["properties"][$key];
				}
			}
		}

		$this->_int_do_implicit_save();
		return $prev;
	}

	function merge($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_MERGE,
				"msg" => "object::merge($param): parameter must be an array of properties to merge!"
			));
		}

		$this->obj += $param;
		$this->_int_do_implicit_save();
	}

	function merge_prop($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_MERGE,
				"msg" => "object::merge_prop($param): parameter must be an array of properties to merge!"
			));
		}

		if (!is_array($this->obj["properties"]))
		{
			$this->obj["properties"] = array();
		}
		foreach($param as $key => $val)
		{
			$this->set_prop($key, $val);
		}
	}

	function properties()
	{
		return $this->obj["properties"];
	}

	function fetch()
	{
		// returns something which resembles the return value of get_object
		// this approach might suck, but it's a awfully big task to convert
		// _everything_ and I'm running out of time
		$retval = array();
		if (is_array($this->obj["properties"]))
		{
			$retval = $this->obj["properties"];
		};
		if (is_array($this->obj))
		{
			$retval = array_merge($retval,$this->obj);
		};
		return $retval;
	}

	function is_cache_dirty()
	{
		return $this->obj["cachedirty"];
	}

	function set_cache_dirty($param = true)
	{
		if (!(is_numeric($param) || is_bool($param)))
		{
			error::throw(array(
				"id" => ERR_CACHE_FLAG,
				"msg" => "object::set_cache_dirty($param): parameter must be integer or boolean!"
			));
		}

		$prev = $this->obj["cachedirty"];

		$this->_int_set_of_value("cachedirty", $param);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function last()
	{
		// god damn, no setter for this or we'll never get rid of it!
		return $this->obj['last'];
	}

	function brother_of()
	{
		return $this->obj['brother_of'];
	}

	function instance()
	{
		$clid = $this->class_id();
		if (!$clid)
		{
			error::throw(array(
				"id" => ERR_OBJ_INSTANCE,
				"msg" => "object::instance(): no object loaded or class id not set!"
			));
		}
		$clss = aw_ini_get("classes");
		return get_instance($clss[$clid]["file"]);
	}

	function create_brother($parent)
	{
		error::throw_if(!$this->obj["oid"], array(
			"id" => ERR_CORE_OID,
			"msg" => "object::create_brother($parent): no object loaded!"
		));

		error::throw_if(!is_oid($parent), array(
			"id" => ERR_CORE_OID,
			"msg" => "object::create_brother($parent): no parent!"
		));

		// make sure brothers are only created for original objects, no n-level brothers!
		if ($this->obj["brother_of"] != $this->obj["oid"])
		{
			$o = obj($this->obj["brother_of"]);
			return $o->create_brother($parent);
		}

		// check if a brother already exists for this object under
		// the $parent menu. 
		$ol = new object_list(array(
			"parent" => $parent,
			"brother_of" => $this->obj["oid"]
		));
		if ($ol->count() > 0)
		{
			$tmp = $ol->begin();
			return $tmp->id();
		}

		return $this->_int_create_brother($parent);
	}

	function is_connected_to($param)
	{
		if (count($this->connections_from($param)) > 0)
		{
			return true;
		}
		return false;
	}

	/////////////////////////////////////////////////////////////////
	// private functions
	
	function _init_empty()
	{
		$this->obj = array();
		$this->obj["properties"] = array();
		$this->implicit_save = false;
	}

	function _int_load($oid)
	{
		if (!$GLOBALS["object_loader"]->ds->can("view", $oid))
		{
			error::throw(array(
				"id" => ERR_ACL,
				"msg" => "object::load($oid): no view access for object $oid!"
			));
		}

		$this->_init_empty();

		// now. we gots to find the class_id of the object
		$this->obj = $GLOBALS["object_loader"]->ds->get_objdata($oid);

		$this->_int_load_properties();

		$this->obj["properties"] = $GLOBALS["object_loader"]->ds->read_properties(array(
			"properties" => $GLOBALS["properties"][$this->obj["class_id"]],
			"tableinfo" => $GLOBALS["tableinfo"][$this->obj["class_id"]],
			"objdata" => $this->obj,
		));


		foreach($GLOBALS["of2prop"][$this->obj["class_id"]] as $key => $val)
		{
			if (!$this->obj["properties"][$key])
			{
				$this->_int_sync_from_objfield_to_prop($key);
			}
		}

		// yeees, this looks weird, BUT it is needed if the loaded object is not actually the one requested
		// this can happen in ds_auto_translation for instance
		$GLOBALS["objects"][$oid] = $this;
		$GLOBALS["objects"][$this->obj["oid"]] = $this;
	}

	function _int_load_properties()
	{
		if (isset($GLOBALS["properties"][$this->obj["class_id"]]) && isset($GLOBALS["tableinfo"][$this->obj["class_id"]]) && isset($GLOBALS["of2prop"][$this->obj["class_id"]]))
		{
			return;
		}

		// then get the properties
		$file = $GLOBALS["cfg"]["classes"][$this->obj["class_id"]]["file"];
		if ($this->obj["class_id"] == 29)
		{
			$file = "doc";
		}
		list($GLOBALS["properties"][$this->obj["class_id"]], $GLOBALS["tableinfo"][$this->obj["class_id"]]) = $GLOBALS["object_loader"]->load_properties(array(
			"file" => $file,
			"clid" => $this->obj["class_id"]
		));

		// also make list of properties that belong to object, so we can keep them 
		// in sync in $this->obj and properties

		// things in this array can be accessed later with $objref->prop("keyname")
		$GLOBALS["of2prop"][$this->obj["class_id"]] = array(
			"brother_of" => "brother_of",
			"parent" => "parent",
			"class_id" => "class_id",
			"lang_id" => "lang_id",
			"period" => "period",
			"periodic" => "periodic",
		);
		foreach($GLOBALS["properties"][$this->obj["class_id"]] as $prop)
		{
			if ($prop['table'] == "objects" && $prop["field"] != "meta")
			{
				$GLOBALS["of2prop"][$this->obj["class_id"]][$prop['name']] = $prop['name'];
			}
		}

	}

	function _int_do_save()
	{
		// first, update modifier fields
		$this->_int_set_of_value("modified", time());
		$this->_int_set_of_value("modifiedby", aw_global_get("uid"));


		if (!is_array($GLOBALS["properties"][$this->obj["class_id"]]))
		{
			$this->_int_load_properties();
		}

		if (!$this->obj["oid"])
		{
			$this->_int_init_new();

			$this->obj["oid"] = $GLOBALS["object_loader"]->ds->create_new_object(array(
				"objdata" => $this->obj,
				"properties" => $GLOBALS["properties"][$this->obj["class_id"]],
				"tableinfo" => $GLOBALS["tableinfo"][$this->obj["class_id"]]
			));
			if (!$this->obj["brother_of"])
			{
				$this->obj["brother_of"] = $this->obj["oid"];
			}
		}

		// now, save objdata
		$GLOBALS["object_loader"]->ds->save_properties(array(
			"objdata" => $this->obj,
			"properties" => $GLOBALS["properties"][$this->obj["class_id"]],
			"tableinfo" => $GLOBALS["tableinfo"][$this->obj["class_id"]],
			"propvalues" => $this->obj["properties"]
		));

		return $this->obj["oid"];
	}


	function _int_do_implicit_save()
	{
		if ($this->implicit_save)
		{
			$this->save();
		}
	}

	function _int_sync_from_objfield_to_prop($ofname)
	{
		// object field changed, sync to properties
		if ($GLOBALS["of2prop"][$this->obj["class_id"]][$ofname] != "")
		{
			$this->obj["properties"][$GLOBALS["of2prop"][$this->obj["class_id"]][$ofname]] = $this->obj[$ofname];
		}
	}

	function _int_path()
	{
		$ret = array();
		$parent = $this->id();
		$cnt = 0;
		while ($parent && $parent != $GLOBALS["cfg"]["__default"]["rootmenu"])
		{
			if ($GLOBALS["object_loader"]->ds->can("view", $parent) && $GLOBALS["object_loader"]->ds->object_exists($parent))
			{
				$t = obj($parent);
				$ret[] = $t;
				$parent = $t->parent();
			}
			else
			{
				// we break here, because if we don't have view access to an object in the path
				// we can't find it's parent and then we're fucked anyway. 
				$parent = 0;
				break;
			}

			$cnt++;

			if ($cnt > 100)
			{
				error::throw(array(
					"id" => ERR_HIER, 
					"msg" => "object::path(".$this->id()."): error in object hierarchy, infinite loop!"
				));
			}
		}
		if ($GLOBALS["object_loader"]->ds->can("view", $GLOBALS["cfg"]["__default"]["rootmenu"]))
		{
			$ret[] = obj($GLOBALS["cfg"]["__default"]["rootmenu"]);
		}
		return array_reverse($ret);
	}

	function _int_can($param)
	{
		return $GLOBALS["object_loader"]->ds->can($param, $this->obj["oid"]);
	}

	function _int_can_save()
	{
		if (is_array($this->obj["parent"]))
		{
			$this->obj["parent"] = $this->obj["parent"]["oid"];
		}
	
		// required params - parent and class_id
		if ($this->obj["parent"] > 0 && $this->obj["class_id"] > 0)
		{
			// acl
			if ($this->obj["oid"])
			{
				if ($this->_int_can("edit"))
				{
					return true;	
				}
				else
				{
					error::throw(array(
						"id" => ERR_ACL,
						"msg" => "object::save(): no acess to save object!"
					));
				}
			}
			else
			{
				if ($GLOBALS["object_loader"]->ds->can("add", $this->obj["parent"]))
				{
					return true;	
				}
				else
				{
					error::throw(array(
						"id" => ERR_ACL,
						"msg" => "object::save(): no acess to save object!"
					));
				}
			}
		}
		
		return false;
	}

	function _int_set_of_value($ofield, $val)
	{
		$this->obj[$ofield] = $val;
		$this->_int_sync_from_objfield_to_prop($ofield);
	}

	function _int_init_new()
	{
		$this->_int_set_of_value("created", time());
		$this->_int_set_of_value("createdby", aw_global_get("uid"));
		$this->_int_set_of_value("hits", 0);
		$this->_int_set_of_value("site_id", $GLOBALS["cfg"]["__default"]["site_id"]);

		// new objects can't be created with deleted status
		if (!$this->obj["status"])
		{
			$this->_int_set_of_value("status", STAT_NOTACTIVE);
		}

		// default to current lang id
		if (!$this->obj["lang_id"])
		{
			$this->_int_set_of_value("lang_id", aw_global_get("lang_id"));
		}
	}

	function _int_is_property($prop)
	{
		return isset($GLOBALS["properties"][$this->obj["class_id"]][$prop]) && is_array($GLOBALS["properties"][$this->obj["class_id"]][$prop]);
	}

	function _int_do_delete($oid)
	{
		post_message_with_param(
			MSG_STORAGE_DELETE,
			$this->obj["class_id"],
			array(
				"oid" => $this->obj["oid"]
			)
		);
		$GLOBALS["object_loader"]->ds->delete_object($oid);
	}

	function _int_create_brother($parent)
	{
		return $GLOBALS["object_loader"]->ds->create_brother(array(
			"objdata" => $this->obj,
			"parent" => $parent
		));
	}
}
?>
