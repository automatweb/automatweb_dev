<?php

class object_list extends _int_obj_container_base
{
	var $ds;	// data source
	var $list;	// array of objects in the current list

	function object_list($param = NULL)
	{
		$this->ds = new _int_obj_ds_local_sql;

		if ($param != NULL)
		{
			$this->filter($param);
		}
	}

	function filter($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "objct_list::filter(): parameter must be an array!"
			));
		}

		$this->_int_filter($param);
	}

	function add($param)
	{
		$this->_int_add_to_list($GLOBALS["object_loader"]->param_to_oid_list($param));
	}

	function remove($param)
	{
		$this->_int_remove_from_list($GLOBALS["object_loader"]->param_to_oid_list($param));
	}

	function remove_all()
	{
		$this->_int_remove_from_list($this->ids());
	}

	function get_at($param)
	{
		return $this->list[$GLOBALS["object_loader"]->param_to_oid($param)];
	}

	function sort_by($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_list::sort_by(): argument must be an array!"
			));
		}

		if ($param["prop"] == "")
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_list::sort_by(): prop argument must be present!"
			));
		}

		$this->_int_sort_list($param["prop"], (($param["order"] == "asc" || !$param["order"]) ? "asc" : "desc"));
	}

	function begin()
 	{
		reset($this->list);
		return $this->next();
	}

	function next()
	{
		list(, $ret) = each($this->list);
		return $ret;
	}

	function end()
	{
		return (!is_object(current($this->list)) ? true : false);
	}

	function foreach_o($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_list::foreach_o(): parameter must be an array"
			));
		}

		if (!isset($param["save"]))
		{
			$param["save"] = true;
		}

		if (!$GLOBALS["object_loader"]->is_object_member_fun($param["func"]))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_list::foreach_o(): $param[func] is not a member function of the object class!"
			));
		}

		for($this->begin(), $cnt = 0; !$this->end(); $o =& $this->next(), $cnt++)
		{
			if (isset($param["params"]))
			{
				call_user_func_array(array(&$o, $func), $param["params"]);
			}
			else
			{
				$o->$func();
			}

			if ($param["save"])
			{
				$o->save();
			}
		}

		return $cnt;
	}

	function foreach_cb($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_list::foreach_cb(): parameter must be an array!"
			));
		}

		if (!isset($param["save"]))
		{
			$param["save"] = true;
		}

		if (is_array($param["func"]))
		{
			if (!method_exists($param["func"][0], $param["func"][1]))
			{
				error::throw(array(
					"id" => ERR_PARAM,
					"msg" => "object_list::foreach_cb(): callback method ".$param["func"][1]." does not exist in class ".get_class($param["func"][1])."!"
				));
			}
			else
			if ($param["func"] == "" || !function_exists($param["func"]))
			{
				error::throw(array(
					"id" => ERR_PARAM,
					"msg" => "object_list::foreach_cb(): callback function $param[func] does not exist!"
				));
			}
		}

		for ($this->begin(), $cnt = 0; !$this->end(); $o =& $this->next(), $cnt++)
		{
			if (is_array($param["func"]))
			{
				$param["func"][0]->$param["func"][1]($o, $param["param"]);
			}
			else
			{
				$param["func"]($o, $param["param"]);
			}

			if ($param["save"])
			{
				$o->save();
			}
		}

		return $cnt;
	}

	function arr()
	{
		return $this->list;
	}

	function from_arr($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_list::from_arr(): parameter must be an array!"
			));
		}

		$l = new object_list();
		$l->list = $param;
		return $l;
	}

	// returns all object id's in the current list
	function ids()
	{
		return array_keys($this->list);
	}

	///////////////////////////////////////
	// internal private functions. call these directly and die.

	function _int_filter($filter)
	{
		$this->_int_init_empty();

		$oids = $this->ds->search($filter);
		foreach($oids as $oid)
		{
			$this->list[$oid] = new object($oid);
		}
	}


	function _int_init_empty()
	{
		$this->list = array();
	}
}

?>
