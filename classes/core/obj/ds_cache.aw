<?php

class _int_obj_ds_cache extends _int_obj_ds_decorator
{
	var $funcs = array(
		"get_objdata",
		"read_properties"
	);

	var $cache = NULL;

	function _int_obj_ds_cache($contained)
	{
		parent::_int_obj_ds_decorator($contained);
		$this->cache = get_instance("cache");
	}

	function get_objdata($oid)
	{
		$ret = $this->_get_cache("get_objdata", $oid);
		if (is_array($ret))
		{
			return $ret;
		}
		else
		{
			$ret = $this->contained->get_objdata($oid);
			$this->_set_cache("get_objdata", $oid, $ret);
			return $ret;
		}
	}

	function read_properties($arr)
	{
		$ret = $this->_get_cache("read_properties", $arr["objdata"]["oid"]);
		if (is_array($ret))
		{
			return $ret;
		}
		else
		{
			$ret = $this->contained->read_properties($arr);
			$this->_set_cache("read_properties", $arr["objdata"]["oid"], $ret);
			return $ret;
		}
	}

	function create_new_object($arr)
	{
		$id =  $this->contained->create_new_object($arr);
		$this->_clear_cache($id);
		return $id;
	}

	function save_properties($arr)
	{
		$this->_clear_cache($arr["objdata"]["oid"]);
		return $this->contained->save_properties($arr);
	}

	function read_connection($id)
	{
		return $this->contained->read_connection($id);
	}

	////
	// !saves connection 
	function save_connection($data)
	{
		return $this->contained->save_connection($data);
	}

	////
	// !deletes connection $id
	function delete_connection($id)
	{
		return $this->contained->delete_connection($id);
	}

	
	////
	// !returns all connections that match filter
	function find_connections($arr)
	{
		return $this->contained->find_connections($arr);
	}


	function delete_object($oid)
	{
		$this->_clear_cache($oid);
		return $this->contained->delete_object($oid);
	}

	function search($params)
	{
		$query_hash = "search::".md5(serialize($params));
		
		$ret = $this->_get_cache($query_hash, $oid);
		if (is_array($ret))
		{
			return $ret;
		}
		else
		{
			$ret = $this->contained->search($params);
			$this->_set_cache($query_hash, $oid, $ret);
			return $ret;
		}
	}

	////////////////////////////////
	// internal cache funcs

	function _get_cache($fn, $oid)
	{
		$fqfn = $GLOBALS["cfg"]["cache"]["page_cache"]."/objcache::$fn::$oid";
		if (file_exists($fqfn))
		{
			include($fqfn);
			return $arr;
		}

		return false;
	}

	function _set_cache($fn, $oid, $dat)
	{
		$str = "<?php\n";
		$str .= aw_serialize($dat, SERIALIZE_PHP_FILE);
		$str .= "?>";

		$this->cache->file_set("objcache::$fn::$oid", $str);
	}

	function _clear_cache($oid)
	{
		foreach($this->funcs as $func)
		{
			$this->cache->file_invalidate("objcache::$func::$oid");
		}
		$this->cache->file_invalidate_regex("objcache::search::(.*)");
	}
}
?>
