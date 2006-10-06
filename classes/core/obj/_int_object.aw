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
		if (!empty($GLOBALS["TRACE_OBJ"]))
		{
			echo "load object $param from <br>".dbg::short_backtrace()." <br>";
		}
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
			error::raise(array(
				"id" => ERR_SAVE,
				"msg" => sprintf(t("object::save(): object (%s) cannot be saved, needed properties are not set (parent, class_id)"), $this->obj["oid"])
			));
			return;
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

	function delete($full_delete = false)
	{
		if (!$this->obj["oid"])
		{
			error::raise(array(
				"id" => ERR_NO_OID,
				"msg" => t("object::delete(): no current object loaded")
			));
			return;
		}
		
		if (!$this->can("delete"))
		{
			error::raise(array(
				"id" => ERR_ACL,
				"msg" => sprintf(t("object::delete(): no delete access for current object (%s)"), $this->obj["oid"])
			));
			return;
		}

		$ret = $this->obj["oid"];

		$this->_int_do_delete($this->obj["oid"], $full_delete);

		return $ret;
	}

	function connect($param)
	{
		if (!is_array($param))
		{
			error::raise(array(
				"id" => ERR_PARAM,
				"msg" => sprintf(t("object::connect(%s): parameter must be an array of connection parameters!"), $param)
			));
			return;
		}

		if (!$param["reltype"] && $param["type"])
		{
			$param["reltype"] = $param["type"];
		}

		$oids = $GLOBALS["object_loader"]->param_to_oid_list($param["to"]);
		foreach($oids as $oid)
		{
			$to = obj($oid);
			$oid = $to->brother_of();

			// check if a connection to the same object with the same reltype already exists
			// if it does, then don't do it again.
			$cprms = array("to" => $oid);
			if ($param["reltype"])
			{
				if (!is_numeric($param["reltype"]) && substr($param["reltype"], 0, 7) == "RELTYPE")
				{
					// it is "RELTYPE_FOO"
					// resolve it to numeric
					$param["reltype"] = $GLOBALS["relinfo"][$this->obj["class_id"]][$param["reltype"]]["value"];
				}
				$cprms["type"] = $param["reltype"];
			}
			
			// use a different code path for not saved objects
			if (!is_oid($this->obj["oid"]))
			{
				// object is not saved, therefore we cannot create
				// the actual connection, so remember the data
				// and try to create it _after_ the object is saved
				$this->obj["_create_connections"][] = $param;
			}
			else
			{
				$tmp = obj_set_opt("no_cache", 1);
				if (count($this->connections_from($cprms)) > 0)
				{
					obj_set_opt("no_cache", $tmp);
					return;
				}
				obj_set_opt("no_cache", $tmp);

				if ($GLOBALS["object_loader"]->ds->can("view", $this->obj["brother_of"]) && 
					$GLOBALS["object_loader"]->ds->can("view", $oid))
				{
					$c = new connection();
					$param["from"] = $this->obj["brother_of"];
					$param["to"] = $oid;
					$c->change($param);
				}
				else
				{
					error::raise(array(
						"id" => ERR_ACL,
						"msg" => sprintf(t("object::connect(): no view access for both endpoints (%s and %s)!"), $this->obj["brother_of"], $oid)
					));
					return;
				}
			};
		}
	}

	function disconnect($param)
	{
		if (!is_array($param))
		{
			error::raise(array(
				"id" => ERR_PARAM,
				"msg" => sprintf(t("object::disconnect(%s): parameter must be an array!"), $param)
			));
			return;
		}

		$oids = $GLOBALS["object_loader"]->param_to_oid_list($param["from"]);
		foreach($oids as $oid)
		{
			$c = new connection();
			$finder = array(
				"from" => $this->obj["brother_of"],
				"from.class_id" => $this->obj["class_id"],
				"to" => $oid,
			);
			if (!empty($param["type"]))
			{
				$finder["type"] = $param["type"];
			}
			$conn_id = $c->find($finder);
			if (count($conn_id) < 1)
			{
				if($param["errors"] === false)
				{
					return false;
				}
				else
				{
					error::raise(array(
						"id" => ERR_CONNECTION,
						"msg" => sprintf(t("object::disconnect(): could not find connection to object %s from object %s"), $oid, $this->obj["oid"])
					));
					return;
				}
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
			error::raise(array(
				"id" => ERR_NO_OBJ,
				"msg" => t("object::connections_from(): no current object loaded!")
			));
			return;
		}

		$filter = array(
			"from" => $this->obj["brother_of"]
		);

		if ($param != NULL)
		{
			if (!is_array($param))
			{
				error::raise(array(
					"id" => ERR_PARAM,
					"msg" => t("object::connections_from(): if argument is present, then argument must be array of filter parameters!")
				));
				return;
			}
			if (isset($param["type"]))
			{
				$param["type"] = $GLOBALS["object_loader"]->resolve_reltype($param["type"], $this->obj["class_id"]);
				if ($param["type"])
				{
					$filter["type"] = $param["type"];
				}
			}
			if (isset($param["to"]))
			{
				$filter["to"] = $GLOBALS["object_loader"]->param_to_oid_list($param["to"]);
			}
			if (isset($param["idx"]))
			{
				$filter["idx"] = $param["idx"];
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

			if (isset($param["class"]))
			{
				$filter["to.class_id"] = $param["class"];
			}
		}

		if (!$filter["from.class_id"])
		{
			$filter["from.class_id"] = $this->obj["class_id"];
		}

		$ret = array();
		$cs = $GLOBALS["object_loader"]->ds->find_connections($filter);
		foreach($cs as $c_id => $c_d)
		{
			// set acldata to memcache
			$GLOBALS["__obj_sys_acl_memc"][$c_d["from"]] = array(
				"acldata" => $c_d["from.acldata"],
				"parent" => $c_d["from.parent"]
			);
			$GLOBALS["__obj_sys_acl_memc"][$c_d["to"]] = array(
				"acldata" => $c_d["to.acldata"],
				"parent" => $c_d["to.parent"]
			);
			if ($GLOBALS["object_loader"]->ds->can("view", $c_d["to"]))
			{
				$ret[$c_id] =& new connection($c_d);
			}
		}
		if ($param["sort_by"] != "")
		{
			uasort($ret, create_function('$a,$b', 'return strcasecmp($a->prop("'.$param["sort_by"].'"), $b->prop("'.$param["sort_by"].'"));'));
		}
		if ($param["sort_by_num"] != "")
		{
			uasort($ret, create_function('$a,$b', 'return ($a->prop("'.$param["sort_by_num"].'") == $b->prop("'.$param["sort_by_num"].'") ? 0 : ($a->prop("'.$param["sort_by_num"].'") > $b->prop("'.$param["sort_by_num"].'") ? 1 : -1 ));'));
		}
		if($param['sort_dir'] == 'desc')
		{
			return array_reverse($ret);
		}
		else
		{
			return $ret;
		}
	}

	function connections_to($param = NULL)
	{
		if (!$this->obj["oid"])
		{
			error::raise(array(
				"id" => ERR_NO_OBJ,
				"msg" => t("object::connections_to(): no current object loaded!")
			));
			return;
		}

		$filter = array(
			"to" => $this->obj["brother_of"]
		);

		if ($param != NULL)
		{
			if (!is_array($param))
			{
				error::raise(array(
					"id" => ERR_PARAM,
					"msg" => t("object::connections_to(): if argument is present, then argument must be array of filter parameters!")
				));
				return;
			}
			if (isset($param["type"]))
			{
				if (!is_numeric($param["type"]) && substr($param["type"], 0, 7) == "RELTYPE" && is_class_id($param["from.class_id"]))
				{
					// it is "RELTYPE_FOO"
					// resolve it to numeric
					if (!is_array($GLOBALS["relinfo"][$param["from.class_id"]]))
					{
						// load class def
						_int_object::_int_load_properties($param["from.class_id"]);
					}

					if (!$GLOBALS["relinfo"][$param["from.class_id"]][$param["type"]]["value"])
					{
						$param["type"] = -1; // won't match anything
					}
					else
					{
						$param["type"] = $GLOBALS["relinfo"][$param["from.class_id"]][$param["type"]]["value"];
					}
				}

				$filter["type"] = $param["type"];
			}
			if (isset($param["from"]))
			{
				$filter["from"] = $GLOBALS["object_loader"]->param_to_oid_list($param["from"]);
			}

			if (isset($param["idx"]))
			{
				$filter["idx"] = $param["idx"];
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

			if (isset($param["class"]))
			{
				$filter["from.class_id"] = $param["class"];
			}
		}

		$ret = array();
		$cs = $GLOBALS["object_loader"]->ds->find_connections($filter);
		foreach($cs as $c_d)
		{
			// set acldata to memcache
			$GLOBALS["__obj_sys_acl_memc"][$c_d["from"]] = array(
				"acldata" => $c_d["from.acldata"],
				"parent" => $c_d["from.parent"]
			);
			$GLOBALS["__obj_sys_acl_memc"][$c_d["to"]] = array(
				"acldata" => $c_d["to.acldata"],
				"parent" => $c_d["to.parent"]
			);	
			if ($GLOBALS["object_loader"]->ds->can("view", $c_d["from"]))
			{
				$ret[] =& new connection($c_d);
			}
		}

		if ($param["sort_by"] != "")
		{
			usort($ret, create_function('$a,$b', 'return strcasecmp($a->prop("'.$param["sort_by"].'"), $b->prop("'.$param["sort_by"].'"));'));
		}
		if ($param["sort_by_num"] != "")
		{
			uasort($ret, create_function('$a,$b', 'return ($a->prop("'.$param["sort_by_num"].'") == $b->prop("'.$param["sort_by_num"].'") ? 0 : ($a->prop("'.$param["sort_by_num"].'") > $b->prop("'.$param["sort_by_num"].'") ? 1 : -1 ));'));
		}

		if($param['sort_dir'] == 'desc')
		{
			return array_reverse($ret);
		}
		else
		{
			return $ret;
		}
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
			error::raise(array(
				"id" => ERR_NO_OBJ,
				"msg" => t("object::path(): no object loaded!")
			));
			return;
		}

		if ($param !== NULL && !is_array($param))
		{
			error::raise(array(
				"id" => ERR_PARAM,
				"msg" => sprintf(t("object::path(%s): if parameter is specified, it must be an array!"), $param)
			));
			return;
		}

		if (is_array($param) && isset($param["to"]))
		{
			$param["to"] = $GLOBALS["object_loader"]->param_to_oid($param["to"]);
		}

		return $this->_int_path($param);
	}

	function path_str($param = NULL)
	{
		if ($param != NULL)
		{
			if (!is_array($param))
			{
				error::raise(array(
					"id" => ERR_PARAM,
					"msg" => t("object::path_str(): parameter must be an array if present!")
				));
				return;
			}
		}

		$pt = $this->path($param);
		$i = 0;
		$cnt = count($pt);
       	if (isset($param["max_len"]))
		{
			$i = $cnt - $param["max_len"];
			if (!empty($param["path_only"]))
			{
				$i--;
			}
		}

		$skip = is_oid($param["start_at"]) ? true : false;
		$ret = array();
		for(; $i < $cnt; $i++)
		{
			if (is_object($pt[$i]))
			{
				if (is_oid($param["start_at"]) && $pt[$i]->id() == $param["start_at"])
				{
					$skip = false;
				}
				if ($skip)
				{
					continue;
				}

				if (!empty($param["path_only"]) && $pt[$i]->id() == $this->obj["oid"])
				{
					continue;
				}
				$ret[] = $pt[$i]->name();
			}
		}
		$tmp = join(" / ", $ret);
		if ($tmp == "" && empty($param["path_only"]))
		{
			$tmp = $this->name();
		}

		return $tmp;
	}

	function can($param)
	{
		if (!$this->obj["oid"])
		{
			error::raise(array(
				"id" => ERR_ACL,
				"msg" => sprintf(t("object::can(%s): no current object loaded!"),$param)
			));
			return;
		}

		return $this->_int_can($param);
	}

	function is_property($param)
	{
		if (!is_string($param))
		{
			error::raise(array(
				"err" => "ERR_PARAM",
				"msg" => sprintf(t("object::is_property(%s): parameter must be a string!"), $param)
			));
			return;
		}

		if (!is_class_id($this->obj["class_id"]))
		{
			error::raise(array(
				"err" => "ERR_NO_CLASS_ID",
				"msg" => sprintf(t("object::is_property(%s): no class_id for the current object is set!"), $param)
			));
			return;
		}

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
			error::raise(array(
				"id" => ERR_NO_PARENT,
				"msg" => sprintf(t("object::set_parent(%s): no parent specified!"), $parent)
			));
			return;
		}


		$this->_int_set_of_value("parent", $parent);
		$this->_int_do_implicit_save();

		// also, check parent object and set site_id according to these rules:
		// - if parent is client type menu, then do nothing
		// - else set site_id same as parent's
		if (is_oid($parent) && $GLOBALS["object_loader"]->ds->can("view", $parent))
		{
			if (is_object($GLOBALS["objects"][$parent]))
			{
				$objdata = $GLOBALS["objects"][$parent]->obj;
			}
			else
			{
				$objdata = $GLOBALS["object_loader"]->ds->get_objdata($parent, array("no_errors" => true));
			}

			if ($objdata !== NULL)
			{
				$o = obj($parent);
				if (!($o->class_id() == CL_MENU && $o->prop("type") == MN_CLIENT) && $o->site_id())
				{
					$this->set_site_id($o->site_id());
				}
			}
		}

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
			error::raise(array(
				"id" => ERR_CLASS_ID,
				"msg" => sprintf(t("object::set_class(%s): specified class id id is not valid!"), $param)
			));
			return;
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
				error::raise(array(
					"id" => ERR_STATUS,
					"msg" => sprintf(t("object::set_status(%s): incorrect status code!"), $param)
				));
				return;
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

	function set_lang_id($param)
	{
		$prev = $this->obj['lang_id'];
		if (!is_numeric($param))
		{
			error::raise(array(
				"id" => "ERR_LANG_ID",
				"msg" => sprintf(t("object::set_lang_id(%s): lang_id must be integer!"), $param)
			));
			return;

		}
		$this->_int_set_of_value("lang_id", (int)$param);
		$this->_int_do_implicit_save();
		return $prev;

	}

	function set_lang($param)
	{
		$prev = $this->lang();

		$li = get_instance("languages");
		$lang_id = $li->get_langid_for_code($param);
		if (!$lang_id)
		{
			$lang_id = aw_global_get("lang_id");
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
		
		if (!is_numeric($param) && $param != "")
		{
			error::raise(array(
				"id" => ERR_ORD,
				"msg" => sprintf(t("object::set_ord(%s): order must be integer!"), $param)
			));
			return;
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
		return $this->obj["createdby"];
	}

	function created()
	{
		return $this->obj["created"];
	}

	function modifiedby()
	{
		return $this->obj["modifiedby"];
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

		if (!is_numeric($param) && $param != "")
		{
			error::raise(array(
				"id" => ERR_PERIOD,
				"msg" => sprintf(t("object::set_period(%s): period must be integer!"), $param)
			));
			return;
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

		if (!(is_numeric($param) || is_bool($param)) && $param != "")
		{
			error::raise(array(
				"id" => ERR_BOOL,
				"msg" => sprintf(t("object::set_periodic(%s): order must be integer or boolean!"), $param)
			));
			return;
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
			error::raise(array(
				"id" => ERR_SITE_ID,
				"msg" => sprintf(t("object::set_site_id(%s): site_id must be integer!"), $param)
			));
			return;
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
		if ($this->is_brother() && 
			$GLOBALS["object_loader"]->ds->can("view", $this->obj["brother_of"])
		)
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

		if ($param  == "")
		{
			$param = 0;
		}

		if (!is_numeric($param))
		{
			error::raise(array(
				"id" => ERR_SUBCLASS,
				"msg" => sprintf(t("object::set_subclass(%s): subclass must be integer!"), $param)
			));
			return;
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

		if ($param  == "")
		{
			$param = 0;
		}

		if (!is_numeric($param))
		{
			error::raise(array(
				"id" => ERR_FLAGS,
				"msg" => sprintf(t("object::set_flags(%s): flags must be integer!"), $param)
			));
			return;
		}

		$this->_int_set_of_value("flags", (int)$param);
		$this->_int_do_implicit_save();
		return $prev;
	}

	function flag($param)
	{
		if (!is_numeric($param))
		{
			error::raise(array(
				"id" => ERR_FLAG,
				"msg" => sprintf(t("object::flag(%s): flag must be integer!"), $param)
			));
			return;
		}

		return $this->obj["flags"] & $param;
	}

	function set_flag($flag, $val)
	{
		if (!is_numeric($flag))
		{
			error::raise(array(
				"id" => ERR_FLAG,
				"msg" => sprintf(t("object::set_flag(%s, %s): flag must be integer!"), $flag, $val)
			));
			return;
		}
		if (!(is_numeric($val) || is_bool($val)))
		{
			error::raise(array(
				"id" => ERR_FLAG,
				"msg" => sprintf(t("object::set_flag(%s, %s): value must be integer!"), $flag, $val)
			));
			return;
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
		// calling this without an argument returns the contents of whole metainfo
		// site_content->build_menu_chain for example needs access to the whole metainfo at once -- duke
		if ($param === false)
		{
			$retval = $this->obj["meta"];
		}
		else
		{
			$retval = isset($this->obj["meta"][$param]) ? $this->obj["meta"][$param] : null;
		};
		return $retval;
	}

	function set_meta($key, $value)
	{
		$prev = $this->obj["meta"][$key];

		$this->_int_set_ot_mod("metadata", $prev, $value);
		$this->obj["meta"][$key] = $value;

		$dat = $GLOBALS["properties"][$this->obj["class_id"]][$key];

		// if any property is defined for metadata, we gots to sync from object to property
		if (is_array($dat) && $dat["field"] == "meta" && $dat["table"] == "objects")
		{
			$this->_int_set_prop($key, $value);
		}

		$this->_int_do_implicit_save();
		return $prev;
	}

	function get_property_list()
	{
		return $GLOBALS["properties"][$this->obj["class_id"]];
	}

	function get_group_list()
	{
		$clid = $this->obj["class_id"];
		$classes = aw_ini_get("classes");
		$inf = $GLOBALS["object_loader"]->load_properties(array(
			"file" => ($clid == CL_DOCUMENT ? "doc" : basename($classes[$clid]["file"])),
			"clid" => $clid
		
		));
		return $inf[4];
	}

	function get_relinfo()
	{
		return $GLOBALS["relinfo"][$this->obj["class_id"]];
	}

	function get_tableinfo()
	{
		return $GLOBALS["tableinfo"][$this->obj["class_id"]];
	}

	function get_classinfo()
	{
		return $GLOBALS["classinfo"][$this->obj["class_id"]];
	}

	function prop($param)
	{
		$retval = $this->_int_get_prop($param);
		return $retval;
	}

	function prop_str($param, $is_oid = NULL)
	{
		$pd = $GLOBALS["properties"][$this->obj["class_id"]][$param];
		if (!$pd)
		{
			return $this->prop($param);
		}

		$type = $pd["type"];
		if ($is_oid)
		{
			$type = "oid";
		}

		$val = $this->_int_get_prop($param);
		switch($type)
		{
			// YOU *CAN NOT* convert dates to strings here - it fucks up dates in vcl tables 
			case "relmanager":
			case "relpicker": 
			case "classificator":
			case "popup_search":
			case "crm_participant_search":
			case "releditor":
				if ($pd["store"] == "connect")
				{
					$rels = new object_list($this->connections_from(array(
						"type" => $pd["reltype"]
					)));
					$_tmp = $rels->names();
					if (count($_tmp))
					{
						$val = join(",", $_tmp);
					}
					else
					{
						$val = "";
					}
					break;
				}

			case "oid":
				if (is_oid($val))
				{
					if ($GLOBALS["object_loader"]->ds->can("view", $val))
					{
						$tmp = new object($val);
						$val = $tmp->name();
					}
					else
					{
						$val = "";
					}
				}
				else
				if (is_array($val))
				{
					$vals = array();
					foreach($val as $k)
					{
						if (is_oid($k))
						{
							if ($GLOBALS["object_loader"]->ds->can("view", $k))
							{
								$tmp = new object($k);
								$vals[] = $tmp->name();
							}
						}
					}
					$val = join(", ", $vals);
				}
				break;
		}
		if ($val === "0" || $val === 0)
		{
			$val = "";
		}
		return $val;
	}

	function set_prop($key, $val)
	{
		if (!$this->_int_is_property($key))
		{
			error::raise(array(
				"id" => ERR_PROP,
				"msg" => sprintf(t("object::set_prop(%s, %s): no property %s defined for current object!"), $key, $val, $key)
			));
			return;
		}

		$prev = $this->_int_get_prop($key);
		$this->_int_set_prop($key, $val);
		// if this is a relpicker property, create the relation as well
		$propi = $GLOBALS["properties"][$this->obj["class_id"]][$key];
		if ((($propi["type"] == "relpicker" ) || 
			($propi["type"] == "releditor" && ($propi["store"] == "connect" || $propi["choose_default"] == 1)) ||
			 $propi["type"] == "relmanager" || 
			($propi["type"] == "classificator" && $propi["store"] == "connect") ||
			($propi["type"] == "popup_search" && $propi["reltype"] != "") ||
			($propi["type"] == "chooser" && $propi["store"] == "connect" || $propi["reltype"] != "")
			))
		{
			$_rt = $GLOBALS["relinfo"][$this->obj["class_id"]][$propi["reltype"]]["value"];
			if ($propi["multiple"] == 1 || is_array($val))
			{
				$tval = $val;
				if (!is_array($tval))
				{
					$tval = array($tval => $tval);
				}
				// get all old connections
				// remove the ones that are not selected
				if (is_oid($this->id()) && ($propi["type"] != "relpicker" || $propi["store"] == "connect"))
				{
					foreach($this->connections_from(array("type" => $_rt)) as $c)
					{
						if (!in_array($c->prop("to"), $tval))
						{
							$this->disconnect(array("from" => $c->prop("to"), "type" => $_rt));
						}
					}
				}
				// connect to all selected ones
				foreach(safe_array($tval) as $_idx => $connect_to)
				{
					if (is_oid($connect_to) && $GLOBALS["object_loader"]->ds->can("view", $connect_to))
					{
						$this->connect(array(
							"to" => $connect_to,
							"reltype" => $_rt
						));
					}
					else
					{
						unset($tval[$_idx]);
					}
				}
			}
			else
			{
				if (is_oid($val) && $GLOBALS["object_loader"]->ds->can("view", $val))
				{
					$this->connect(array(
						"to" => $val,
						"reltype" => $_rt
					));
				}
			}
		}

		// if this is an object field property, sync to object field
		if ($propi["table"] == "objects")
		{
			if ($propi["field"] == "meta")
			{
				$this->_int_set_ot_mod("metadata", $this->obj["meta"][$propi["name"]], $val);
				$this->obj["meta"][$propi["name"]] = $val;
			}
			else
			if ($propi["method"] == "bitmask")
			{
				// it's flags, sync to that
				$mask = $this->obj["flags"];
				// zero out cur field bits
				$mask = $mask & (~((int)$propi["ch_value"]));
				$mask = $mask | $val;
				$this->_int_set_ot_mod("flags", $this->obj["flags"], $mask);
				$this->obj["flags"] = $mask;
			}
			else
			{
				if ($propi["method"] == "serialize")
				{
					$this->_int_set_ot_mod($propi["field"], $this->obj[$propi["field"]][$propi["name"]], $this->obj["properties"][$key]);
					$this->obj[$propi["field"]][$propi["name"]] = $this->obj["properties"][$key];
				}
				else
				{
					$this->_int_set_ot_mod($propi["field"], $this->obj[$propi["field"]], $this->obj["properties"][$key]);
					$this->obj[$propi["field"]] = $this->obj["properties"][$key];
				}
			}
		}
		$this->_int_do_implicit_save();
		return $prev;
	}

	function properties()
	{
		// make sure props are loaded
		$this->_int_get_prop(NULL);
		$ret = $this->obj["properties"];
		$ret["createdby"] = $this->createdby();
		$ret["modifiedby"] = $this->modifiedby();
		$ret["created"] = $this->created();
		$ret["modified"] = $this->modified();
		return $ret;
	}

	function fetch()
	{
		// returns something which resembles the return value of get_object
		// this approach might suck, but it's a awfully big task to convert
		// _everything_ and I'm running out of time
		$this->_int_get_prop(NULL);
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
		if (!(is_numeric($param) || is_bool($param)) && $param != "")
		{
			error::raise(array(
				"id" => ERR_CACHE_FLAG,
				"msg" => sprintf(t("object::set_cache_dirty(%s): parameter must be integer or boolean!"), $param)
			));
			return;
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
			error::raise(array(
				"id" => ERR_OBJ_INSTANCE,
				"msg" => t("object::instance(): no object loaded or class id not set!")
			));
			return;
		}
		$clss = aw_ini_get("classes");
		return get_instance($clss[$clid]["file"]);
	}

	function create_brother($parent)
	{
		if (!$this->obj["oid"])
		{
			error::raise(array(
				"id" => ERR_CORE_OID,
				"msg" => sprintf(t("object::create_brother(%s): no object loaded!"), $parent)
			));
			return;
		}

		if (!is_oid($parent))
		{
			error::raise(array(
				"id" => ERR_CORE_OID,
				"msg" => sprintf(t("object::create_brother(%s): no parent!"), $parent)
			));
			return;
		}

		// make sure brothers are only created for original objects, no n-level brothers!
		if ($this->obj["brother_of"] != $this->obj["oid"])
		{
			$o = obj($this->obj["brother_of"]);
			return $o->create_brother($parent);
		}

		// check if a brother already exists for this object under
		// the $parent menu. 
		$noc = obj_set_opt("no_cache", 1);
		$ol = new object_list(array(
			"parent" => $parent,
			"brother_of" => $this->obj["oid"],
			"site_id" => array(),
		));
		if ($ol->count() > 0)
		{
			$tmp = $ol->begin();
			obj_set_opt("no_cache", $noc);
			return $tmp->id();
		}

		obj_set_opt("no_cache", $noc);
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

	function set_create_new_version()
	{
		$this->_create_new_version = 1;
	}

	function load_version($v)
	{
		$GLOBALS["object2version"][$this->obj["oid"]] = $v;
		$this->_int_load($this->obj["oid"]);
	}

	function set_save_version($v)
	{
		$GLOBALS["object2version"][$this->obj["oid"]] = $v;
	}

	function set_no_modify($arg)
	{
		$this->no_modify = $arg;
	}

	function originalize()
	{
		if (!$this->is_brother())
		{
			return;
		}
		$GLOBALS["object_loader"]->ds->originalize($this->obj["oid"]);
	}

	function trans_get_val($prop)
	{
		switch($prop)
		{
			case "name":
				if ($this->class_id() == CL_LANGUAGE)
				{
					$val = $this->prop("lang_name");
					$prop = "lang_name";
				}
				else
				{
					$val = $this->$prop();
				}
				break;
	
			default:
				$val = $this->prop($prop);
		}
		
		$trans = false;
		$cur_lid = false;
		if ($GLOBALS["cfg"]["user_interface"]["content_trans"] == 1 && ($cur_lid = aw_global_get("lang_id")) != $this->lang_id())
		{
			$trans = true;
		}

		if ($GLOBALS["cfg"]["user_interface"]["full_content_trans"] == 1 && ($cl = aw_global_get("ct_lang_id")) != $this->lang_id())
		{
			$trans = true;
			$cur_lid = $cl;
		}

		if ($trans)
		{
			$trs = $this->obj["meta"]["translations"];
			if (isset($trs[$cur_lid]) && $this->obj["meta"]["trans_".$cur_lid."_status"] == 1)
			{
				if ($trs[$cur_lid][$prop] == "")
				{
					return $val;
				}
				$val = $trs[$cur_lid][$prop];
			}
		}
		
		return $val;	
	}

	function trans_get_val_str($prop)
	{
		$val = $this->prop_str($prop);
		if ($val === "0" || $val === 0)
		{
			$val = "";
		}

		if ($GLOBALS["cfg"]["user_interface"]["content_trans"] == 1 && ($cur_lid = aw_global_get("lang_id")) != $this->lang_id())
		{
			$trs = $this->obj["meta"]["translations"];
			if (isset($trs[$cur_lid]))
			{
				if ($trs[$cur_lid][$prop] == "")
				{
					return $val;
				}
				$val = $trs[$cur_lid][$prop];
			}
		}
		return $val;
	}	

	/////////////////////////////////////////////////////////////////
	// private functions
	
	function _init_empty()
	{
		$this->obj = array();
		$this->obj["properties"] = array();
		$this->obj['class_id'] = null;
		$this->obj['meta'] = null;
		$this->implicit_save = false;
		$this->props_loaded = false;
		$this->props_modified = array();
		$this->ot_modified = array("modified" => 1);
	}

	function _int_set_prop_mod($prop, $oldval, $newval)
	{
		$cv1 = $oldval;
		$cv2 = $newval;
		if ($cv1 === "" && $cv2 === "0")
		{
			$cv1 = "0";
		}
		if ($cv1 === "0" && $cv2 === "")
		{
			$cv2 = "0";
		}
		if (is_array($cv1) || is_array($cv2))
		{
			$cv1 = serialize($cv1);
			$cv2 = serialize($cv2);
		}
		if ($cv1 != $cv2)
		{
			$this->props_modified[$prop] = 1;
		}
	}

	function _int_set_ot_mod($fld, $oldval, $newval)
	{
		$cv1 = $oldval;
		$cv2 = $newval;
		if (is_array($cv1) || is_array($cv2))
		{
			$cv1 = serialize($cv1);
			$cv2 = serialize($cv2);
		}
		if ($cv1 != $cv2 && isset($GLOBALS["object_loader"]->all_ot_flds[$fld]))
		{
			$this->ot_modified[$fld] = 1;
		}
	}

	function _int_load($oid)
	{
		if (!$GLOBALS["object_loader"]->ds->can("view", $oid))
		{
			error::raise(array(
				"id" => ERR_ACL,
				"msg" => sprintf(t("object::load(%s): no view access for object %s!"), $oid, $oid)
			));
			return;
		}
		$this->_init_empty();

		// now. we gots to find the class_id of the object
		if (isset($GLOBALS["__obj_sys_objd_memc"][$oid]))
		{
			$this->obj = $GLOBALS["__obj_sys_objd_memc"][$oid];
			unset($GLOBALS["__obj_sys_objd_memc"][$oid]);
		}
		else
		{
			$this->obj = $GLOBALS["object_loader"]->ds->get_objdata($oid);
		}

		$this->_int_load_property_values();

		// now that we know the class id, change the object instance out from beneath us, if it is set so in the ini file
		$cld = $GLOBALS["cfg"]["__default"]["classes"][$this->obj["class_id"]];
		if (!empty($cld["object_override"]))
		{
			$i = get_instance($cld["object_override"]);
			// copy props
			$i->obj = $this->obj;
			$i->implicit_save = $this->implicit_save;
			$i->props_loaded = $this->props_loaded;
			$i->obj_sys_flags = $this->obj_sys_flags;
			$i->obj_sys_flags = $GLOBALS["__obj_sys_opts"];
			if (method_exists($i, "_init_override_object"))
			{
				$i->_init_override_object();
			}
			$GLOBALS["objects"][$oid] = $i;
			$GLOBALS["objects"][$this->obj["oid"]] = $i;
		}
		else
		{
			// yeees, this looks weird, BUT it is needed if the loaded object is not actually the one requested
			$GLOBALS["objects"][$oid] = $this;
			if ($oid !== $this->obj["oid"])
			{
				$GLOBALS["objects"][$this->obj["oid"]] = $this;
			}
		}
	}

	function _int_load_properties($cl_id = NULL)
	{
		if ($cl_id === NULL)
		{
			$cl_id = $this->obj["class_id"];
		}

		if (isset($GLOBALS["properties"][$cl_id]) && isset($GLOBALS["tableinfo"][$cl_id]) && isset($GLOBALS["of2prop"][$cl_id]))
		{
			return;
		}

		// then get the properties
		if ($cl_id == 29)
		{
			$file = "doc";
		}
		else
		{
			$file = empty($GLOBALS["cfg"]["classes"][$cl_id]["file"]) ? null : $GLOBALS["cfg"]["classes"][$cl_id]["file"];
		}
		list(
				$GLOBALS["properties"][$cl_id], 
				$GLOBALS["tableinfo"][$cl_id], 
				$GLOBALS["relinfo"][$cl_id],
				$GLOBALS["classinfo"][$cl_id],
			) = 
			$GLOBALS["object_loader"]->load_properties(array(
				"file" => $file,
				"clid" => $cl_id
		));

		if (!isset($GLOBALS["properties"][$cl_id]))
		{
			$GLOBALS["properties"][$cl_id] = "";
		}

		if (!isset($GLOBALS["tableinfo"][$cl_id]))
		{
			$GLOBALS["tableinfo"][$cl_id] = "";
		}

		// also make list of properties that belong to object, so we can keep them 
		// in sync in $this->obj and properties

		// things in this array can be accessed later with $objref->prop("keyname")
		$GLOBALS["of2prop"][$cl_id] = array(
			"brother_of" => "brother_of",
			"parent" => "parent",
			"class_id" => "class_id",
			"lang_id" => "lang_id",
			"period" => "period",
			"created" => "created",
			"modified" => "modified",
			"periodic" => "periodic",
		);
		foreach($GLOBALS["properties"][$cl_id] as $prop)
		{
			if ($prop['table'] == "objects" && $prop["field"] != "meta")
			{
				$GLOBALS["of2prop"][$cl_id][$prop['name']] = $prop['name'];
			}
		}
	}

	function _int_do_save()
	{
		// first, update modifier fields
		
		if (!$this->no_modify)
		{
			$this->_int_set_of_value("modified", time());
			$this->_int_set_of_value("modifiedby", aw_global_get("uid"));
		}



		if (!is_array($GLOBALS["properties"][$this->obj["class_id"]]))
		{
			$this->_int_load_properties();
		}

		$_is_new = false;
		if (!$this->obj["oid"])
		{
			$this->_int_init_new();

			$this->_int_do_inherit_new_props();

			$this->obj["oid"] = $GLOBALS["object_loader"]->ds->create_new_object(array(
				"objdata" => &$this->obj,
				"properties" => $GLOBALS["properties"][$this->obj["class_id"]],
				"tableinfo" => $GLOBALS["tableinfo"][$this->obj["class_id"]]
			));

			if (!$this->obj["brother_of"])
			{
				$this->obj["brother_of"] = $this->obj["oid"];
			}
			$_is_new = true;
		}
		else
		{
			// check if the class specifies that it is versioned and that something has changed
			if ($GLOBALS["classinfo"][$this->obj["class_id"]]["versioned"] == 1 && aw_ini_get("config.object_versioning"))
			{
				if (count($this->ot_modified) > 1 || count($this->props_modified) > 1)
				{
					$GLOBALS["object_loader"]->ds->backup_current_version(array(
						"properties" => $GLOBALS["properties"][$this->obj["class_id"]],
						"tableinfo" => $GLOBALS["tableinfo"][$this->obj["class_id"]],
						"id" => $this->obj["oid"]
					));
				}
			}

			// now, save objdata
			$GLOBALS["object_loader"]->ds->save_properties(array(
				"objdata" => $this->obj,
				"properties" => $GLOBALS["properties"][$this->obj["class_id"]],
				"tableinfo" => $GLOBALS["tableinfo"][$this->obj["class_id"]],
				"propvalues" => $this->obj["properties"],
				"ot_modified" => $this->ot_modified,
				"props_modified" => $this->props_modified,
				"create_new_version" => $this->_create_new_version
			));

			$this->ot_modified = array("modified" => 1);
			$this->props_modified = array();
		}

		if (is_array($this->obj["_create_connections"]))
		{
			foreach($this->obj["_create_connections"] as $new_conn)
			{
				//$obj = obj($this->obj["oid"]);
				if (is_oid($new_conn["to"]))
				{
					$this->connect($new_conn);
				}
			};
		};

		// obj inherit props impl
		$this->_int_do_obj_inherit_props();

		// if this is a brother object, we should save the original as well
		if ($this->obj["oid"] != $this->obj["brother_of"])
		{
			// first, unload the object
			// of course, we will lose data here if it is modified, but this is a race condition anyway.
			unset($GLOBALS["objects"][$this->obj["brother_of"]]);
			obj_set_opt("no_cache", 1);
			$original = obj($this->obj["brother_of"]);
			obj_set_opt("no_cache", 0);
			$original->save();
		}

		// log save
		$GLOBALS["object_loader"]->_log($_is_new, $this->obj["oid"], $this->obj["name"], $this->obj["class_id"]);

		return $this->obj["oid"];
	}

	function _int_do_inherit_new_props()
	{
		$data = $GLOBALS["object_loader"]->obj_inherit_props_conf;
		if (!is_array($data))
		{
			return;
		}
		foreach($data as $from_oid => $ihd)
		{
			if (is_array($ihd))
			{
				foreach($ihd as $r_ihd)
				{
					if ($r_ihd["to_class"] == $this->obj["class_id"] && (!is_array($r_ihd["only_to_objs"]) || count($r_ihd["only_to_objs"]) == 0))
					{
						if ($GLOBALS["object_loader"]->ds->can("edit", $from_oid))
						{
							$orig = obj($from_oid);
							$this->_int_set_prop_mod($r_ihd["to_prop"], $this->obj["properties"][$r_ihd["to_prop"]], $orig->prop($r_ihd["from_prop"]));
							$this->obj["properties"][$r_ihd["to_prop"]] = $orig->prop($r_ihd["from_prop"]);
						}
					}
				}
			}		
		}
	}

	function _int_do_obj_inherit_props()
	{
		if (isset($GLOBALS["object_loader"]->obj_inherit_props_conf[$this->obj["oid"]]))
		{
			$tmp = safe_array($GLOBALS["object_loader"]->obj_inherit_props_conf[$this->obj["oid"]]);
			foreach($tmp as $ihd)
			{
				$propv = $this->obj["properties"][$ihd["from_prop"]];
			
				// find all object os correct type
				$filt = array(
					"class_id" => $ihd["to_class"],
					"site_id" => array(),
					"lang_id" => array()
				);
				if (is_array($ihd["only_to_objs"]) && count($ihd["only_to_objs"]) > 0)
				{
					$filt["oid"] = $ihd["only_to_objs"];
				}
				$ol = new object_list($filt);
				foreach($ol->arr() as $o)
				{
					$o->set_prop($ihd["to_prop"], $propv);
					$o->save();
				}
			}
		}
	}

	function _int_do_implicit_save()
	{
		if ($this->implicit_save)
		{
			$this->save();
		}
	}

	function _int_sync_from_objfield_to_prop($ofname, $mod = true)
	{
		// object field changed, sync to properties
		$pn = $GLOBALS["of2prop"][$this->obj["class_id"]][$ofname];
		if ($pn != "")
		{
			if ($mod)
			{
				$this->_int_set_prop_mod($pn, $this->obj["properties"][$pn], $this->obj[$ofname]);
			}
//			$this->obj["properties"][$pn] = isset($this->obj[$ofname]) ? $this->obj[$ofname] : null;
			// For whatever reason, upper row breaks shit. And i mean shit, like, the whole AW.
			$this->obj["properties"][$pn] = $this->obj[$ofname];
		}
	}

	function _int_path($param)
	{
		$ret = array();
		$parent = $this->id();
		$cnt = 0;

		if (is_admin())
		{
			$rootmenu = (array)$GLOBALS["cfg"]["__default"]["admin_rootmenu2"];
			$add = false;
		}
		else
		{
			$rootmenu = array($GLOBALS["cfg"]["__default"]["rootmenu"]);
			$add = true;
		}
		
		while ($parent && !in_array($parent, $rootmenu))
		{
			if ($GLOBALS["object_loader"]->ds->can("view", $parent))
			{
				unset($t);
				$t = new object($parent);

				if (is_oid($param["to"]) && $t->id() == $param["to"])
				{
					$add = false;
					break;
				}

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
				error::raise(array(
					"id" => ERR_HIER, 
					"msg" => sprintf(t("object::path(%s): error in object hierarchy, infinite loop!"), $this->id())
				));
				return;
			}
		}

		if ($add && !aw_global_get("__is_install"))
		{
			$rm = reset($rootmenu);
			if ($GLOBALS["object_loader"]->ds->can("view", $rm))
			{
				$ret[] = obj($rm);
			}
		}

		$ret = array_reverse($ret);
		if ($param["no_self"])
		{
			array_pop($ret);
		}
		return $ret;
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
					error::raise(array(
						"id" => ERR_ACL,
						"msg" => sprintf(t("object::save(): no acess to save object %s under %s !"), $this->obj["oid"], $this->obj["parent"])
					));
					return;
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
					error::raise(array(
						"id" => ERR_ACL,
						"msg" => sprintf(t("object::save(): no access to add object under folder %s (gidlist = %s)!"), $this->obj["parent"], join(",", aw_global_get("gidlist")))
					));
					return;
				}
			}
		}
		
		return false;
	}

	function _int_set_of_value($ofield, $val)
	{
		$this->_int_set_ot_mod($ofield, $this->obj[$ofield], $val);
		$this->obj[$ofield] = $val;
		$this->_int_sync_from_objfield_to_prop($ofield);
	}

	function _int_init_new()
	{
		$this->_int_set_of_value("created", time());
		$this->_int_set_of_value("createdby", aw_global_get("uid"));
		$this->_int_set_of_value("hits", 0);
		if (!$this->obj["site_id"])
		{
			$this->_int_set_of_value("site_id", $GLOBALS["cfg"]["__default"]["site_id"]);
		}

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

	function _int_do_delete($oid, $full_delete = false)
	{
		// load the object to see of its brother status
		$obj = $GLOBALS["object_loader"]->ds->get_objdata($oid);

		$todelete = array();		

		// if this object is a brother to another object, just delete it.
		if ($obj["brother_of"] != $oid)
		{
			$todelete[] = $oid;
		}
		// else, if this is an original object
		else
		{
			// find all of its brothers and delete all of them. 
			list($tmp) = $GLOBALS["object_loader"]->ds->search(array(
				"brother_of" => $oid,
				"lang_id" => array(),
				"site_id" => array()
			));
			$todelete = array_keys($tmp);
		}

		foreach($todelete as $oid)
		{			
			if (!$GLOBALS["object_loader"]->ds->can("delete", $oid))
			{
				continue;
			}
			post_message_with_param(
				MSG_STORAGE_DELETE,
				$this->obj["class_id"],
				array(
					"oid" => $oid
				)
			);

			$tmpo = obj($oid);
			// get object's class info
			$clid = $tmpo->class_id();
			if ($clid == 7)
			{
				$type = ST_DOCUMENT;
			}
			else
			{
				$type = @constant($GLOBALS["classinfo"][$clid]["syslog_type"]["text"]);
			}
			if (!$type)
			{
				$type = 10000;
			}
			$nm = $tmpo->name();

			if ($full_delete)
			{
				$GLOBALS["object_loader"]->ds->final_delete_object($oid);
				$GLOBALS["object_loader"]->cache->_log($type, SA_FINAL_DELETE, $nm, $oid, false);
			}
			else
			{
				$GLOBALS["object_loader"]->ds->delete_object($oid);
				$GLOBALS["object_loader"]->cache->_log($type, SA_DELETE, $nm, $oid, false);
			}
		}

		// must clear acl cache for all objects below it
		// but since I think that finding the subobjects and clearing just those will be slower than clearing it all
		// we're gonna clear it all.
		if (!aw_ini_get("acl.use_new_acl"))
		{
			$c = get_instance("cache");
			$c->file_clear_pt("acl");
		}
	}

	function _int_create_brother($parent)
	{
		return $GLOBALS["object_loader"]->ds->create_brother(array(
			"objdata" => $this->obj,
			"parent" => $parent
		));
	}

	function _int_set_prop($prop, $val)
	{
		if (!$this->props_loaded)
		{
			$this->_int_load_property_values();
		}
		$this->_int_set_prop_mod($prop, $this->obj["properties"][$prop], $val);
		$this->obj["properties"][$prop] = $val;
	}

	function _int_get_prop($prop)
	{
		if (!$this->props_loaded)
		{
			$this->_int_load_property_values();
		}

		// if this is a complex thingie, then loopdaloop
		if (strpos($prop, ".") !== false)
		{
			$o = $this;
			foreach(explode(".", $prop) as $part)
			{
				$cur_v = $o->prop($part);
				$prop_dat = $GLOBALS["properties"][$o->class_id()][$part];
				if (in_array($prop_dat["type"], array("relpicker", "classificator", "popup_search", "relmanager", "releditor")))
				{
					if (is_array($cur_v) && count($cur_v) == 1)
					{
						$cur_v = reset($cur_v);
					}
					if (!$GLOBALS["object_loader"]->ds->can("view", $cur_v))
					{
						return null;
					}
					$o = obj($cur_v);
				}
				else
				{
					return $cur_v;
				}
			}
			return $cur_v;
		}

		if (isset($GLOBALS["properties"][$this->obj["class_id"]][$prop]))
		{
			$pd = $GLOBALS["properties"][$this->obj["class_id"]][$prop];
		}
		if ($pd && $pd["field"] == "meta" && $pd["table"] == "objects")
		{
			return isset($this->obj["meta"][$pd["name"]]) ? $this->obj["meta"][$pd["name"]] : null;
		}

		return $this->obj["properties"][$prop];
	}

	function _int_load_property_values()
	{
		$this->_int_load_properties();

		if (!is_oid($this->obj["oid"]))
		{
			// do not try to read an empty object
			return;
		}

		$this->obj["properties"] = $GLOBALS["object_loader"]->ds->read_properties(array(
			"properties" => $GLOBALS["properties"][$this->obj["class_id"]],
			"tableinfo" => $GLOBALS["tableinfo"][$this->obj["class_id"]],
			"objdata" => $this->obj,
		));

		foreach(safe_array($GLOBALS["of2prop"][$this->obj["class_id"]]) as $key => $val)
		{
			//if (!$this->obj["properties"][$key])
			if (empty($this->obj["properties"][$key]))
			{
				$this->_int_sync_from_objfield_to_prop($key, false);
			}
		}
		$this->props_loaded = true;
	}
}

?>
