<?php
// $Header: /home/cvs/automatweb_dev/classes/core/obj/object_list.aw,v 1.28 2004/07/07 18:10:04 kristo Exp $
// object_list.aw - with this you can manage object lists

class object_list extends _int_obj_container_base
{
	var $list = array();	// array of objects in the current list

	function object_list($param = NULL)
	{
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

		if (is_array($param["oid"]) && sizeof($param["oid"]) == 0)
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "objct_list::filter(): oid parameter cannot be an empty array!"
			));

		}

		// we should check the individual arguments as well .. if "oid" is an object
		// (aw_array) .. then this thingie will return absurd results .. like 
		// for id-s of all documents in the database.. yehh ...it happened
		// in UT --duke

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
		return $this->_int_get_at($GLOBALS["object_loader"]->param_to_oid($param));
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

	function sort_by_cb($cb)
	{
		if (is_array($cb))
		{
			error::throw_if(!is_object($cb[0]), array(
				"id" => ERR_CORE_NO_OBJ,
				"msg" => "object_list::sort_by_cb(): if parameter is an array, the first entry must be an object instance!"
			));

			error::throw_if(!method_exists($cb[0], $cb[1]), array(
				"id" => ERR_CORE_NO_OBJ,
				"msg" => "object_list::sort_by_cb(): if parameter is an array, the first entry must be an object instance and the second a method name from that object!"
			));
		}
		else
		{
			error::throw_if(!function_exists($cb),array(
				"id" => ERR_CORE_NO_FUNC,
				"msg" => "object_list::sort_by_cb($cb): no function $cb exists!"
			));
		}
		$this->_int_sort_list_cb($cb);
	}


	function begin()
 	{
		// here's how begin/next are supposed to work:
		// begin returns the first item, does not advance iterator
		// next 1st advances the iterator, them returns current item
		// then end will be correct even for 1 element lists!
		$this->iter_index = 0;
		$this->iter_lut = array_keys($this->list);
		$this->iter_lut_count = count($this->iter_lut);

		return $this->_int_get_at($this->iter_lut[$this->iter_index]);
	}

	function next()
	{
		$this->iter_index++;
		return $this->_int_get_at($this->iter_lut[$this->iter_index]);
	}

	function end()
	{
		return (($this->iter_index) == ($this->iter_lut_count));
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
			if ($param["func"] == "delete")
			{
				$param["save"] = false;
			}
			else
			{
				$param["save"] = true;
			}
		}

		$func = $param["func"];

		if (!$GLOBALS["object_loader"]->is_object_member_fun($func))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_list::foreach_o(): $param[func] is not a member function of the object class!"
			));
		}

		for($o =& $this->begin(), $cnt = 0; !$this->end(); $o =& $this->next(), $cnt++)
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

		if (is_array($param["func"]))
		{
			if (!method_exists($param["func"][0], $param["func"][1]))
			{
				error::throw(array(
					"id" => ERR_PARAM,
					"msg" => "object_list::foreach_cb(): callback method ".$param["func"][1]." does not exist in class ".get_class($param["func"][1])."!"
				));
			}
		}
		else
		if ($param["func"] == "" || !function_exists($param["func"]))
		{
			error::throw(array(
				"id" => ERR_PARAM,
				"msg" => "object_list::foreach_cb(): callback function $param[func] does not exist!"
			));
		}

		// why not foreach($this->list as $item)? it works just as well, and is
		// easier on the eyes -- duke
		// 
		// because then I will not have to reimplement lazy loading here. ever heard of encapsulation?
		// -- terryf

		for ($o =& $this->begin(), $cnt = 0; !$this->end(); $o =& $this->next(), $cnt++)
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
		$ret = array();
		for ($o =& $this->begin(), $cnt = 0; !$this->end(); $o =& $this->next(), $cnt++)
		{
			$ret[$o->id()] = $o;
		}
		return $ret;
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

	////
	// !Some kind of replacement for core->object_list .. returns id=>name pairs of objects
	// parameters:
	//	$add_folders - if true, objects paths are returned instead of just names
	function names($arr = array())
	{
		if (isset($arr["add_folders"]) && $arr["add_folders"])
		{
			$ret = array();
			for ($o =& $this->begin(), $cnt = 0; !$this->end(); $o =& $this->next(), $cnt++)
			{
				$ret[$o->id()] = $o->path_str();
			}
			return $ret;
		}
		else
		{
			return $this->list_names;
		}
	}

	// returns all object id's in the current list
	function ids()
	{
		return array_keys($this->list);
	}

	
	function count()
	{
		return count($this->list);
	}

	///////////////////////////////////////
	// internal private functions. call these directly and die.

	function _int_filter($filter)
	{
		$this->_int_init_empty();

		$tmp = $GLOBALS["object_loader"]->ds->search($filter);
		list($oids, $meta_filter) = $tmp;
		if (!is_array($oids))
		{
			return false;
		};

		if (count($meta_filter) > 0)
		{
			foreach($oids as $oid => $oname)
			{
				if ($GLOBALS["object_loader"]->ds->can("view", $oid))
				{
					$add = true;
					$_o = new object($oid);
					foreach($meta_filter as $mf_k => $mf_v)
					{
						$tmp = $_o->meta($mf_k);
						if (is_numeric($mf_v))
						{
							$tmp = (int)$tmp;
							$mf_v = (int)$mf_v;
						}
						if ($tmp != $mf_v)
						{
							$add = false;
						}
					}

					if ($add)
					{
						$this->list[$oid] = $_o;
						$this->list_names[$oid] = $oname;
					}
				}
			}
		}
		else
		{
			foreach($oids as $oid => $oname)
			{
				if ($GLOBALS["object_loader"]->ds->can("view", $oid))
				{
					$this->list[$oid] = $oid;
					$this->list_names[$oid] = $oname;
				}
			}
		}

	}


	function _int_init_empty()
	{
		$this->list = array();
		$this->list_names = array();
	}

	function _int_sort_list($prop, $order)
	{
		$this->_sby_prop = $prop;
		$this->_sby_order = $order;

		$this->_int_sort_list_cb(array(&$this, "_int_sort_list_default_sort"));
	
		unset($this->_sby_prop);
		unset($this->_sby_order);
	}

	function _int_sort_list_default_sort($a, $b)
	{
		if ($a->prop($this->_sby_prop) == $b->prop($this->_sby_prop))
		{
			return 0;
		}

		if ($a->prop($this->_sby_prop) < $b->prop($this->_sby_prop))
		{
			if ($this->_sby_order == "asc")
			{
				return -1;
			}
			else
			{
				return 1;
			}
		}

		if ($a->prop($this->_sby_prop) > $b->prop($this->_sby_prop))
		{
			if ($this->_sby_order == "asc")
			{
				return 1;
			}
			else
			{
				return -1;
			}
		}

	}

	function _int_add_to_list($oid_arr)
	{
		foreach($oid_arr as $oid)
		{
			$this->list[$oid] = new object($oid);
			$this->list_names[$oid] = $this->list[$oid]->name();
		}
	}

	function _int_get_at($oid)
	{
		if (!is_object($this->list[$oid]))
		{
			$this->list[$oid] = new object($oid);
		}
		return $this->list[$oid];
	}

	function _int_sort_list_cb($cb)
	{
		// cb is checked before getting here
		
		$this->cb = $cb;
		uksort($this->list, array(&$this, "_int_sort_list_cb_cb"));
		unset($this->cb);
	}

	function _int_sort_list_cb_cb($a, $b)
	{
		$a_o = $this->_int_get_at($a);
		$b_o = $this->_int_get_at($b);
		if (is_array($this->cb))
		{
			$tcb = $this->cb;
			return $tcb[0]->$tcb[1]($a_o, $b_o);
		}
		else
		{
			return $this->cb($a_o, $b_o);
		}
	}

	function _int_remove_from_list($oid_l)
	{
		foreach($oid_l as $oid)
		{
			unset($this->list[$oid]);
			unset($this->list_names[$oid]);
		}
	}
}

?>
