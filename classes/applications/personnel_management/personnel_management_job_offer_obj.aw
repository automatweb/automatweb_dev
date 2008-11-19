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
	
	function get_candidates($arr = array())
	{
		$ret = new object_list();

		$i = get_instance(CL_FILE);
		foreach(parent::connections_from(array("type" => 1)) as $conn)
		{
			if(!isset($arr["status"]) || $conn->conn["to.status"] == $arr["status"])
			{
				$to = $conn->to();
				if($i->can("view", $to->prop("person")))
				{
					$ret->add($to->prop("person"));
				}
			}
		}

		return $ret;
	}

	/**
		@attrib name=handle_show_cnt api=1 params=name

		@param action required type=string

		@param id required type=OID

	**/
	public function handle_show_cnt($arr)
	{
		extract($arr);

		$show_cnt_conf = get_instance("personnel_management_obj")->get_show_cnt_conf();
		$usr = get_instance(CL_USER);
		$u = $usr->get_current_user();
		$g = $show_cnt_conf[CL_PERSONNEL_MANAGEMENT_JOB_OFFER][$action]["groups"];
		if($usr->is_group_member($u, $g) && is_oid($id))
		{
			$o = obj($id);
			$o->show_cnt = $o->show_cnt + 1;
			aw_disable_acl();
			$o->save();
			aw_restore_acl();
		}
	}
}

?>
