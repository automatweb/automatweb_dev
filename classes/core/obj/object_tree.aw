<?php

class object_tree extends _int_obj_container_base
{
	/////////////////////////////////////////////
	// private variables
	var $tree;	// array - tree structure, 2 level - level one is parent, level 2 is oid => object

	////////////////////////////////////////////
	// public functions

	function object_tree($param)
	{
		if (!$param != NULL && !is_array($param))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_tree:conectructor(): if you specify a parameter, it must be a filter array!"
			));
		}

		$this->_int_init_empty();		

		if (is_array($param))
		{
			$this->_int_load($param);
		}
	}

	function filter($filter, $new = true)
	{
		if (!is_array($filter))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_tree::filter(): filter parameter must be an array!"
			));
		}

		if ($new)
		{
			$this->_int_load($filter);
		}
		else
		{
			$this->_int_filter_current($filter);
		}
	}

	function to_list()
	{
		$ol = new object_list();
		foreach($this->tree as $pt => $objs)
		{
			foreach($objs as $oid => $o)
			{
				$ol->add($oid);
			}
		}
		return $ol;
	}

	function foreach_o($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_tree::foreach_o(): parameter must be an array"
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
				"msg" => "object_tree::foreach_o(): $param[func] is not a member function of the object class!"
			));
		}

		$cnt = 0;
		foreach($this->tree as $_pt => $level)
		{
			foreach($level as $_oid => $o)
			{
				$cnt++;
	
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
		}

		return $cnt;
	}

	function foreach_cb($param)
	{
		if (!is_array($param))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_tree::foreach_cb(): parameter must be an array!"
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
					"msg" => "object_tree::foreach_cb(): callback method ".$param["func"][1]." does not exist in class ".get_class($param["func"][1])."!"
				));
			}
		}
		else
		if ($param["func"] == "" || !function_exists($param["func"]))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_tree::foreach_cb(): callback function ".$param["func"][1]." does not exist!"
			));
		}

		$cnt = 0;
		foreach($this->tree as $_pt => $level)
		{
			foreach($level as $_oid => $o)
			{
				if (is_array($param["func"]))
				{
					$meth = $param["func"][1];
					$param["func"][0]->$meth($o, $param["param"]);
				}
				else
				{
					$param["func"]($o, $param["param"]);
				}

				if ($param["save"])
				{
					$o->save();
				}
				$cnt++;
			}
		}

		return $cnt;
	}

	function level($param)
	{
		$oid = $GLOBALS["object_loader"]->param_to_oid($param);
		return (is_array($this->tree[$oid]) ? $this->tree[$oid] : array());
	}

	function subtree($param)
	{
		$oid = $GLOBALS["object_loader"]->param_to_oid($param);
		return $this->_int_subtree($oid);
	}

	function add($param)
	{
		$cnt = 0;
		$oids = $GLOBALS["object_loader"]->param_to_oid_list($param);
		foreach($oids as $oid)
		{
			$o = new object($oid);
			$this->tree[$o->parent()][$o->id()] = $o;
			$cnt++;
		}
		return $cnt;
	}

	function remove($param)
	{
		$cnt = 0;
		$oids = $GLOBALS["object_loader"]->param_to_oid_list($param);
		foreach($oids as $oid)
		{
			$o = new object($oid);
			$this->_int_req_remove($o, $cnt);
		}
		return $cnt;
	}

	function remove_all()
	{
		$this->_int_init_empty();
	}

	function ids()
	{
		$ret = array();
		foreach($this->tree as $_pt => $level)
		{
			foreach($level as $oid => $o)
			{
				$ret[] = $oid;
			}
		}

		return $ret;
	}

	///////////////////////////////////////////////
	// internal private functions. call these directly and die.

	function _int_init_empty()
	{
		$this->tree = array();
	}

	function _int_load($filter)
	{
		// load using only lists, not datasource. funky.
		if (!$filter["parent"])
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_tree::filter(): parent filter parameter mut always be passed!"
			));
		}

		$filter["parent"] = $GLOBALS["object_loader"]->param_to_oid($filter["parent"]);

		$this->_int_init_empty();
		$this->_int_req_filter($filter);
	}

	function _int_req_filter($filter)
	{
		$oids = $GLOBALS["object_loader"]->ds->search($filter);
		foreach($oids as $oid)
		{
			$o = new object($oid);
			$this->tree[$o->parent()][$o->id()] = $o;

			// why should this be in here, if it can be out there? --duke

			/*
			$filter["parent"] = $oid;
			$this->_int_req_filter($filter);
			*/
		}
		if (sizeof($oids) > 0)
		{
			$filter["parent"] = $oids;
			$this->_int_req_filter($filter);
		};
	}

	function _int_subtree($parent)
	{
		$ol = new object_tree();
		$this->_int_req_subtree($parent, $ol);
		return $ol;
	}	

	function _int_req_subtree($parent, &$ol)
	{
		foreach($this->level($parent) as $oid => $o)
		{
			$ol->tree[$o->parent()][$oid] = $o;
			$this->_int_req_subtree($oid);
		}
	}

	function _int_req_remove(&$o, &$cnt)
	{
		if (is_array($this->tree[$o->id()]))
		{
			foreach($this->tree[$o->id()] as $oid => $_o)
			{
				$this->_int_req_remove($_o, $cnt);
			}
			unset($this->tree[$o->id()]);
		}
		if (isset($this->tree[$o->parent()][$o->id()]))
		{
			unset($this->tree[$o->parent()][$o->id()]);
			$cnt++;
		}
	}

	function _int_filter_current($filter)
	{
		foreach($this->tree as $_pt => $level)
		{
			foreach($level as $oid => $o)
			{
				$arr = $o->arr();
				foreach($filter as $k => $v)
				{
					if ($arr[$k] != $v)
					{
						$this->remove($oid);
						break;
					}
				}
			}
		}
	}
}

?>
