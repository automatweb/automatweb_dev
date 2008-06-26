<?php

class personnel_management_job_offer_obj extends _int_object
{
	function set_prop($k, $v)
	{
		if($k == "notify_me")
		{
			$p = get_instance(CL_USER)->get_person_for_uid(aw_global_get("uid"));
			if($v == 1)
			{
				parent::connect(array(
					"to" => $p->id(),
					"type" => "RELTYPE_NOTIFY_ME",
				));
			}
			else
			if(is_oid(parent::id()))
			{
				$conns = connection::find(array(
					"from" => parent::id(),
					"to" => $p->id(),
					"reltype" => "RELTYPE_NOTIFY_ME",
				));
				if(count($conns) > 0)
				{
					parent::disconnect(array(
						"from" => $p->id(),
						"type" => "RELTYPE_NOTIFY_ME",
					));
				}
			}
			return true;
		}
		else
		{
			return parent::set_prop($k, $v);
		}
	}

	function get_end()
	{
		if($this->prop("endless"))
		{
			return t("T&auml;htajatu");
		}
		return get_lc_date($this->prop("end"));
	}

	function prop($k)
	{
		if($k == "notify_me")
		{
			$p = get_instance(CL_USER)->get_person_for_uid(aw_global_get("uid"));
			$conns = connection::find(array(
				"from" => parent::id(),
				"to" => $p->id(),
				"reltype" => "RELTYPE_NOTIFY_ME",
			));
			if(count($conns) > 0)
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}
		else
		if($k == "end" && $this->prop("endless"))
		{
			// Endless - as BIG as possible!
			return pow(2, 31) - 1;
		}
		else
		{
			return parent::prop($k);
		}
	}
	
	function get_candidates()
	{
		$ret = new object_list();

		$i = get_instance(CL_FILE);
		foreach(parent::connections_from(array("type" => 1)) as $conn)
		{
			if(!isset($arr["status"]) || $conn->conn["to.status"] == $arr["status"])
			{
				$to = $conn->to();
				$acl_ok = true;
				foreach(obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault())->needed_acl_candidate as $acl)
				{
					$acl_ok = $acl_ok && $this->can($acl, $to->prop("person"));
				}
				if($i->can("view", $to->prop("person")) && $acl_ok)
				{
					$ret->add($to->prop("person"));
				}
			}
		}

		return $ret;
	}
}

?>