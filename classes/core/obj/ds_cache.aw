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
		$res = $this->_get_cache("search_".$id, 0, "connection");
		if (!is_array($res))
		{
			$res = $this->contained->read_connection($id);
			$this->_set_cache("search_".$id, 0, $res, "connection");
		}
		return $res;
	}

	////
	// !saves connection 
	function save_connection($data)
	{
		$this->_clear_cache(0, "connection");
		return $this->contained->save_connection($data);
	}

	////
	// !deletes connection $id
	function delete_connection($id)
	{
		$this->_clear_cache(0, "connection");
		return $this->contained->delete_connection($id);
	}

	
	////
	// !returns all connections that match filter
	function find_connections($arr)
	{
		$query_hash = md5(serialize($arr));

		$res = $this->_get_cache("search::".$query_hash, 0, "connection");
		if (!is_array($res))
		{
			$res = $this->contained->find_connections($arr);
			$this->_set_cache("search::".$query_hash, 0, $res, "connection");
		}
		return $res;
	}


	function delete_object($oid)
	{
		$this->_clear_cache($oid);
		$this->_clear_cache($oid, "connection");
		return $this->contained->delete_object($oid);
	}

	function search($params)
	{
		$tp = $params;
		if (!isset($tp["lang_id"]))
		{
			$tp["lang_id"] = aw_global_get("lang_id");
		}
		if (!isset($tp["site_id"]))
		{
			$tp["site_id"] = aw_ini_get("site_id");
		}
		$query_hash = "search::".md5(serialize($tp));

		$ret = $this->_get_cache($query_hash, 0);
		if (is_array($ret))
		{
			return $ret;
		}
		else
		{
			$ret = $this->contained->search($params);
			$this->_set_cache($query_hash, 0, $ret);
			return $ret;
		}
	}

	////////////////////////////////
	// internal cache funcs

	function _get_cache($fn, $oid, $cfn = "objcache")
	{
		$fqfn = $GLOBALS["cfg"]["cache"]["page_cache"]."/".$cfn."::$fn::$oid";
		if (file_exists($fqfn))
		{
			include($fqfn);
			return $arr;
		}

		return false;
	}

	function _set_cache($fn, $oid, $dat, $cfn = "objcache")
	{
		$GLOBALS["object_loader"]->ds->dequote(&$dat);
		$str = "<?php\n";
		$str .= aw_serialize($dat, SERIALIZE_PHP_FILE);
		$str .= "?>";

		$this->cache->file_set($cfn."::$fn::$oid", $str);
	}

	function _clear_cache($oid, $cfn = "objcache")
	{
		foreach($this->funcs as $func)
		{
			$this->cache->file_invalidate($cfn."::$func::$oid");
		}
		$this->cache->file_invalidate_regex($cfn."::search::(.*)::0");
	}

	function object_exists($oid)
	{
		return $this->contained->object_exists($oid);
	}
}
?>
