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

	function get_objdata($oid, $param = array())
	{
		// check if it is in the cache
		if (isset($this->contained->read_properties_data_cache[$oid]))
		{
			return $this->contained->get_objdata($oid, $param);
		}

		$ret = $this->_get_cache("get_objdata", $oid);
		if (is_array($ret))
		{
			return $ret;
		}
		else
		{
			$ret = $this->contained->get_objdata($oid, $param);
			$this->_set_cache("get_objdata", $oid, $ret);
			return $ret;
		}
	}

	function read_properties($arr)
	{
		// check if it is in the cache
		if (isset($this->contained->read_properties_data_cache[$arr["objdata"]["oid"]]))
		{
			return $this->contained->get_objdata($arr["objdata"]["oid"], $param);
		}

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

	function create_brother($arr)
	{
		$id =  $this->contained->create_brother($arr);
		$this->_clear_cache($id);
		return $id;
	}

	function save_properties($arr)
	{
		$ret = $this->contained->save_properties($arr);
		// fetch brothers and clear cache for all of them

		list($tarr) = $this->contained->search(array(
			"brother_of" => $arr["objdata"]["brother_of"],
		));

		$tarr[$arr["objdata"]["oid"]] = 1;
		$char = array_keys($tarr);
		foreach($char as $obj_id)
		{
			$this->_clear_cache($obj_id);
		}
		return $ret;
	}

	function read_connection($id)
	{
		$this->conn_cache_is_cleared = false;
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
		$this->conn_cache_is_cleared = false;
		$query_hash = md5(serialize($arr));
		$res = $this->_get_cache("search-".$query_hash, 0, "connection");
		if (!is_array($res))
		{
			$res = $this->contained->find_connections($arr);
			$this->_set_cache("search-".$query_hash, 0, $res, "connection");
		}
		return $res;
	}


	function delete_object($oid)
	{
		$this->_clear_cache($oid);
		$this->_clear_cache(0, "connection");
		// on delete we also have to clear the acl cache for that object
		$this->cache->file_invalidate_regex("acl-cache-".$oid."(.*)");
		$this->cache->file_invalidate_regex("get_objdata-".$oid."(.*)");
		aw_cache_flush("__aw_acl_cache");
		return $this->contained->delete_object($oid);
	}

	function search($params, $to_fetch = NULL)
	{
		$this->search_cache_is_cleared = false;
		$tp = $params;
		if (!isset($tp["lang_id"]))
		{
			$tp["lang_id"] = aw_global_get("lang_id");
		}
		if (!isset($tp["site_id"]))
		{
			$tp["site_id"] = aw_ini_get("site_id");
		}
		$query_hash = "search-".md5(serialize($tp).serialize($to_fetch));

		$ret = $this->_get_cache($query_hash, 0);
		if (is_array($ret))
		{
			return $ret;
		}
		else
		{
			$ret = $this->contained->search($params, $to_fetch);
			$this->_set_cache($query_hash, 0, $ret);
			return $ret;
		}
	}

	////////////////////////////////
	// internal cache funcs

	function _get_cache($fn, $oid, $cfn = "objcache")
	{
		if (!empty($GLOBALS["__obj_sys_opts"]["no_cache"]))
		{
			return false;
		}
		$fqfn = $this->cache->get_fqfn($cfn."-$fn-$oid");
		if (file_exists($fqfn))
		{
			include($fqfn);
			if ($GLOBALS["INTENSE_DUKE"] == 1)
			{
				echo "_get_cache look for file ".$cfn."-$fn-$oid got result <br>";
			}
			return $arr;
		}

			if ($GLOBALS["INTENSE_DUKE"] == 1)
			{
				echo "CACHE MISS _get_cache look for file ".$cfn."-$fn-$oid  <br>";
			}
		return false;
	}

	function _set_cache($fn, $oid, $dat, $cfn = "objcache")
	{
		if ($GLOBALS["__obj_sys_opts"]["no_cache"] == 1)
		{
			return false;
		}
		$str = "<?php\n";
		$str .= aw_serialize($dat, SERIALIZE_PHP_FILE);
		$str .= "?>";

			if ($GLOBALS["INTENSE_DUKE"] == 1)
			{
				echo "_set_cache set file ".$cfn."-$fn-$oid  <br>";
			}
		$this->cache->file_set($cfn."-$fn-$oid", $str);
	}

	function _clear_cache($oid, $cfn = "objcache")
	{
		foreach($this->funcs as $func)
		{
			if ($GLOBALS["INTENSE_DUKE"] == 1)
			{
				echo "CLEAR CACHE file  ".$cfn."-$func-$oid got result <br>";
			}
			$this->cache->file_invalidate($cfn."-$func-$oid");
		}
		// if it is connection, then the next invalidate_regex will nuke it anyway
		if ("connection" != $cfn)
		{
			// this flag is false by default. 
			// now, when we clear the cache, we say that it is cleared
			// and if another clear request comes in, before any searches are done
			// we check the flag and don't clear the cache, because nothing will have been written to it
			// the flag gets set whenever a search cache is created
			if (true || !$this->search_cache_is_cleared)
			{
				$this->cache->file_invalidate_regex($cfn."-search-(.*)");
				$this->cache->file_invalidate_regex($cfn."-fetch_list-(.*)");
				$this->search_cache_is_cleared = true;
				$this->cache->flush_cache();
				if ($GLOBALS["INTENSE_DUKE"] == 1)
				{
					echo "CLEAR CACHE file  ".$cfn."-search-(.*) got result <br>";
				}
			}
		};

		if (!$this->conn_cache_is_cleared)
		{
			$this->conn_cache_is_cleared = true;
			$this->cache->file_invalidate_regex("connection(.*)");
			if ($GLOBALS["INTENSE_DUKE"] == 1)
			{
					echo "CLEAR CACHE file  connection(.*) got result <br>";
			}
		}

		// flush menu cache only once per request
		if (!$this->menu_cache_flushed)
		{
			$this->cache->file_invalidate_regex("menuedit-menu_cache(.*)");
			$this->menu_cache_flushed = true;
		}

		$full_flush = !$GLOBALS["__obj_sys_opts"]["no_full_flush"];
		if ("objcache" == $cfn && $full_flush)
		{
			// this is a VERY expensive query and up until now it was done
			// when doing connections_from
			$this->cache->flush_cache();
		};
	}

	function fetch_list($param)
	{
		$this->search_cache_is_cleared = false;
		$hash = "fetch_list-".md5(serialize($param));
		$ret = $this->_get_cache($hash, 0);
		if (is_array($ret))
		{
			foreach($ret as $row)
			{
				$this->contained->read_properties_data_cache[$row["oid"]] = $row;
			}
		}
		else
		{
			$ret = $this->contained->fetch_list($param);
			$this->_set_cache($hash, 0, $ret);
		}
	}
}
?>
