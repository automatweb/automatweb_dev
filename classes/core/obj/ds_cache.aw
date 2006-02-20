<?php

class _int_obj_ds_cache extends _int_obj_ds_decorator
{
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

		if (!empty($GLOBALS["__obj_sys_opts"]["no_cache"]))
		{
			return $this->contained->get_objdata($oid, $param);
		}

		$c_fn = "objdata-$oid";

		$ret = $this->cache->file_get_pt_oid(
			"storage_object_data",
			$oid,
			$c_fn
		);

		if (!is_array($ret))
		{
			$ret = $this->contained->get_objdata($oid, $param);
			$this->cache->file_set_pt_oid(
				"storage_object_data",
				$oid,
				$c_fn,
				$ret
			);
		}
		return $ret;
	}

	function read_properties($arr)
	{
		$oid = $arr["objdata"]["oid"];

		// check if it is in the cache
		if (isset($this->contained->read_properties_data_cache[$oid]))
		{
			return $this->contained->get_objdata($oid, $param);
		}

		if (!empty($GLOBALS["__obj_sys_opts"]["no_cache"]))
		{
			return $this->contained->read_properties($arr);
		}

		$c_fn = "properties-$oid";

		$ret = $this->cache->file_get_pt_oid(
			"storage_object_data",
			$oid,
			$c_fn
		);

		if (!is_array($ret))
		{
			$ret = $this->contained->read_properties($arr);
			$this->cache->file_set_pt_oid(
				"storage_object_data",
				$oid,
				$c_fn,
				$ret
			);
		}
		return $ret;
	}

	function create_new_object($arr)
	{
		$id =  $this->contained->create_new_object($arr);

		// after creating a new object, we need to clear storage search cache and html cache
		// but html cache clear is done in ds_mysql, not here
		$this->cache->file_clear_pt("storage_search");

		return $id;
	}

	function create_brother($arr)
	{
		$id =  $this->contained->create_brother($arr);

		// after creating a new object, we need to clear storage search cache and html cache
		// but html cache clear is done in ds_mysql, not here
		$this->cache->file_clear_pt("storage_search");

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
			$this->cache->file_clear_pt_oid_fn("storage_object_data", $obj_id, "objdata-$obj_id");
			$this->cache->file_clear_pt_oid_fn("storage_object_data", $obj_id, "properties-$obj_id");
		}

		$this->cache->file_clear_pt("storage_search");

		return $ret;
	}

	function read_connection($id)
	{
		if (!empty($GLOBALS["__obj_sys_opts"]["no_cache"]))
		{
			return $this->contained->read_connection($id);
		}

		$c_fn = "connection-$id";

		$ret = $this->cache->file_get_pt_oid(
			"storage_object_data",
			$id,
			$c_fn
		);

		if (!is_array($ret))
		{
			$ret = $this->contained->read_connection($id);
			$this->cache->file_set_pt_oid(
				"storage_object_data",
				$id,
				$c_fn,
				$ret
			);
		}
		return $ret;
	}

	////
	// !saves connection 
	function save_connection($data)
	{
		$ret =  $this->contained->save_connection($data);

		// here we must clear storage search, because it can contain searches by conn and that connection's cache
		// also html cache, but that gets done one level deeper
		if ($data["id"])
		{
			$this->cache->file_clear_pt_oid_fn("storage_object_data", $data["id"], "connection-".$data["id"]);
		}
		
		$this->cache->file_clear_pt("storage_search");

		return $ret;
	}

	////
	// !deletes connection $id
	function delete_connection($id)
	{
		$ret = $this->contained->delete_connection($id);

		// here we must clear storage search, because it can contain searches by conn and that connection's cache
		// also html cache, but that gets done one level deeper
		$this->cache->file_clear_pt_oid_fn("storage_object_data", $id, "connection-".$id);
		$this->cache->file_clear_pt("storage_search");

		return $ret;
	}

	
	////
	// !returns all connections that match filter
	function find_connections($arr)
	{
		if (!empty($GLOBALS["__obj_sys_opts"]["no_cache"]))
		{
			return $this->contained->find_connections($arr);
		}

		$query_hash = md5(serialize($arr));

		$c_fn = "conn_find-$query_hash";

		$ret = $this->cache->file_get_pt(
			"storage_search",
			$query_hash[0],
			$c_fn
		);

		if (!is_array($ret))
		{
			$ret = $this->contained->find_connections($arr);
			$this->cache->file_set_pt(
				"storage_search",
				$query_hash[0],
				$c_fn,
				$ret
			);
		}
		return $ret;
	}


	function delete_object($oid)
	{
		$ret = $this->contained->delete_object($oid);
		aw_cache_flush("__aw_acl_cache");

		// clear lots of caches here: html, acl for oid, storage_search, storage_objdata for $oid
		$this->cache->file_clear_pt_oid("acl", $oid);
		$this->cache->file_clear_pt("storage_search");
		$this->cache->file_clear_pt_oid_fn("storage_object_data", $oid, "objdata-$oid");
		$this->cache->file_clear_pt_oid_fn("storage_object_data", $oid, "properties-$oid");
		
		return $ret;
	}

	function search($params, $to_fetch = NULL)
	{
		if (!empty($GLOBALS["__obj_sys_opts"]["no_cache"]))
		{
			return $this->contained->search($params, $to_fetch);
		}

		$tp = $params;
		if (!isset($tp["lang_id"]))
		{
			$tp["lang_id"] = aw_global_get("lang_id");
		}
		if (!isset($tp["site_id"]))
		{
			$tp["site_id"] = aw_ini_get("site_id");
		}
		$query_hash = md5(serialize($tp).serialize($to_fetch));

		$c_fn = "obj_find-$query_hash";

		$ret = $this->cache->file_get_pt(
			"storage_search",
			$query_hash[0],
			$c_fn
		);

		if (!is_array($ret))
		{
			$ret = $this->contained->search($params, $to_fetch);
			$this->cache->file_set_pt(
				"storage_search",
				$query_hash[0],
				$c_fn,
				$ret
			);
		}
		return $ret;
	}

	function fetch_list($param)
	{
		if (!empty($GLOBALS["__obj_sys_opts"]["no_cache"]))
		{
			return $this->contained->fetch_list($param);
		}

		$query_hash = md5(serialize($param));

		$c_fn = "obj_fetch-$query_hash";

		$ret = $this->cache->file_get_pt(
			"storage_search",
			$query_hash[0],
			$c_fn
		);

		if (!is_array($ret))
		{
			$ret = $this->contained->fetch_list($param);
			$this->cache->file_set_pt(
				"storage_search",
				$query_hash[0],
				$c_fn,
				$ret
			);
		}
		else
		{
			foreach($ret as $row)
			{
				$this->contained->read_properties_data_cache[$row["oid"]] = $row;
			}
		}
		return $ret;
	}
}
?>
