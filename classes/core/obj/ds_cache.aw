<?php

class _int_obj_ds_cache extends _int_obj_ds_decorator
{
	var $funcs = array(
		"get_objdata",
		"read_properties"
	);

	function _int_obj_ds_cache($contained)
	{
		parent::_int_obj_ds_decorator($contained);
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
	
		$fqfn = $GLOBALS["cfg"]["cache"]["page_cache"]."/objcache::$fn::$oid";
		$fp = fopen($fqfn,"w");
		flock($fp, LOCK_EX);
		fwrite($fp, $str);
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	function _clear_cache($oid)
	{
		foreach($this->funcs as $func)
		{
			$fqfn = $GLOBALS["cfg"]["cache"]["page_cache"]."/objcache::$unc::$oid";
			unlink($fqfn);
		}
	}

	function save_properties($arr)
	{
		$this->_clear_cache($arr["objdata"]["oid"]);
		return $this->contained->save_properties($arr);
	}

	function delete_object($oid)
	{
		$this->_clear_cache($oid);
		return $this->contained->delete_object($oid);
	}
}
?>
