<?php

class crm_phone_obj extends _int_object
{
	/**
		@param id required type=oid,array(oid)
	**/
	function get_persons($arr)
	{
		$ret = new object_list;

		// The phone might be connected to the person via work relation.
		$cs = connection::find(array(
			"from" => array(),
			"to" => $arr["id"],
			"type" => "RELTYPE_PHONE",
			"from.class_id" => CL_CRM_PERSON_WORK_RELATION,
		));
		if(count($cs) > 0)
		{
			$wrids = array();
			foreach($cs as $c)
			{
				$wrids[] = $c["from"];
			}
			$cs = connection::find(array(
				"from" => array(),
				"to" => $wrids,
				"from.class_id" => CL_CRM_PERSON,
			));
			foreach($cs as $c)
			{
				$ret->add($c["from"]);
			}
		}
		$cs = connection::find(array(
			"from" => array(),
			"to" => $arr["id"],
			"type" => "RELTYPE_PHONE",
			"from.class_id" => CL_CRM_PERSON,
		));
		foreach($cs as $c)
		{
			$ret->add($c["from"]);
		}

		return $ret;
	}

	function set_prop($k, $v)
	{
		if($k == "name")
		{
			parent::set_prop("clean_number", preg_replace("/[^0-9]/", "", $v));
			//parent::set_prop("clean_number", str_replace(array(" ", "-", "(", ")") , "", $v));
		}
		return parent::set_prop($k, $v);
	}

	function save()
	{
		$oid = parent::id();

		$conn_id = parent::prop("conn_id");
		// This is not supposed to be saved.
		parent::set_prop("conn_id", NULL);
		$conn_ids = isset($conn_id) ? (is_array($conn_id) ? $conn_id : array($conn_id)) : array();

		// New
		if(!is_oid($oid))
		{
			return parent::save();
		}

		// If no connections remain with the old phone obj, there's no point in keeping it. So we'll just change the current one.
		if(count($this->conns_remain_unchanged($conn_ids)) == 0)
		{
			return parent::save();
		}

		// Getting the current name..
		$q = oql::compile_query("SELECT name FROM CL_CRM_PHONE WHERE CL_CRM_PHONE.oid = %u");
		$r = oql::execute_query($q, $oid);
		
		$nname = parent::prop("name");
		$cname = $r[$oid]["name"];
		
		if($nname !== $oname)
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_PHONE,
				"name" => $nname,
				"lang_id" => array(),
				"site_id" => array(),
				"limit" => 1,
			));
			if($ol->count() > 0)
			{
				$pho = $ol->begin();
			}
			else
			{
				$pho = obj($this->save_new());
				$pho->name = $nname;
				$pho->save();
			}

			if(count($conn_ids) > 0)
			{
				foreach($conn_ids as $conn_id)
				{
					if(!is_numeric($conn_id))
					{
						continue;
					}
					try
					{
						$c = new connection();
						$c->load($conn_id);
						$c->change(array(
							"from" => $c->prop("from") == $oid ? $pho->id() : $c->prop("from"),
							"to" => $c->prop("to") == $oid ? $pho->id() : $c->prop("to"),
						));
						$c->save();
					}
					catch (Exception $e)
					{
					}
				}
			}

			// To prevent the original object's name from changing
			parent::set_prop("name", $cname);
		}
		return parent::save();
	}

	private function conns_remain_unchanged($conns)
	{
		$r = array();
		foreach(parent::connections_from(array()) as $c)
		{
			if(!in_array($c->id(), $conns))
			{
				$r[] = $c;
			}
		}
		foreach(parent::connections_to(array()) as $c)
		{
			if(!in_array($c->id(), $conns))
			{
				$r[] = $c;
			}
		}
		return $r;
	}
}

?>
