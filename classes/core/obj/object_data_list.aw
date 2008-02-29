<?php
/*
@classinfo  maintainer=kristo
*/

// take the filter from an object_list, make the where from that
// but take the fetch from another array and allow sql funcs in that
// and return just the data

class object_data_list
{
	function object_data_list($param, $props)
	{
		if (!is_array($param))
		{
			error::raise(array(
				"id" => "ERR_PARAM",
				"msg" => t("object_data_list::object_data_list($param): parameter must be array!")
			));
		}

		$this->_int_load($param, $props);
		$this->props = $props;
	}

	

	/** returns an array of all the objects in the list
		@attrib api=

		@errors
			none

		@returns
			array of data of objects in the list, array key is object id, value is object instance

		@examples
			$odl = new object_data_list(
				array(
					"class_id" => CL_FILE
				),
				array(
					CL_FILE => array("oid" => "id", "name"),
				)
			);
			$files_data = $odl->arr();
	**/
	function arr()
	{
		$arr = array();
		$p = &$this->props;
		foreach($this->list_data as $oid => $od)
		{
			$cp = $p[$od["class_id"]];
			foreach($od as $ode_i => $ode_v)
			{
				if(array_key_exists($ode_i, $cp))
				{
					$arr[$oid][$cp[$ode_i]] = $ode_v;
				}
				elseif(in_array($ode_i, $cp) && is_int(array_search($ode_i, $cp)))
				{
					$arr[$oid][$ode_i] = $ode_v;
				}
			}
		}
		return $arr;
	}

	////////// private

	function _int_load($arr, $props)
	{
		$this->_int_init_empty();
		list($oids, $meta_filter, $acldata, $parentdata, $objdata, $data, $has_sql_func) = $GLOBALS["object_loader"]->ds->search($arr, $props);

		if ($has_sql_func)
		{
			// no acl or anything with sql functions
			$this->list_data = $data;
			return;
		}

		if (!is_array($oids))
		{
			return false;
		};

		// set acldata to memcache
		if (is_array($acldata))
		{
			foreach($acldata as $a_oid => $a_dat)
			{
				$a_dat["status"] = $objdata[$a_oid]["status"];
				$GLOBALS["__obj_sys_acl_memc"][$a_oid] = $a_dat;
			}
		}
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
						if (is_object($mf_v))
						{
							error::raise(array(
								"id" => "ERR_META_FILTER",	
								"msg" => sprintf(t("object_list::filter(%s => %s): can not complex searches on metadata fields!"), $mf_k, $mf_v)
							));
						}
						if ($mf_v{0} == "%")
						{
							error::raise(array(
								"id" => "ERR_META_FILTER",	
								"msg" => sprintf(t("object_list::filter(%s => %s): can not do LIKE searches on metadata fields!"), $mf_k, $mf_v)
							));
						}

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
						$this->list_objdata[$oid] = $objdata[$oid];
					}
				}
			}
		}
		else
		{
			enter_function("object_list::acl_check");
			foreach($oids as $oid => $oname)
			{
				if ($GLOBALS["object_loader"]->ds->can("view", $oid))
				{
					$this->list[$oid] = $oid;
					$this->list_data[$oid] = $data[$oid];
					$this->list_names[$oid] = $oname;
					$this->list_objdata[$oid] = $objdata[$oid];
				}
			}
			exit_function("object_list::acl_check");
		}
	}


	function _int_init_empty()
	{
		$this->list = array();
		$this->list_names = array();
		$this->list_objdata = array();
		$this->list_data = array();
	}
}
?>
