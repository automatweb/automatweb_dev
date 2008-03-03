<?php

class crm_person_obj extends _int_object
{
	public function awobj_set_is_quickmessenger_enabled($value)
	{
		if (1 === $value and 1 === $this->prop("is_quickmessenger_enabled"))
		{
			// delete old box and its messages
		}
		elseif (1 === $value and 0 === $this->prop("is_quickmessenger_enabled"))
		{
			// create&connect box
		}
	}

	function prop($k)
	{
		if ($k == "work_contact")
		{
			return $this->find_work_contact();
		}
		return parent::prop($k);
	}

	function find_work_contact()
	{
		$o = obj($this->id());
		if (!is_oid($o->id()))
		{
			return false;
		}
		$org_rel = $o->get_first_obj_by_reltype("RELTYPE_CURRENT_JOB");
		if (!$org_rel)
		{
			return false;
		}
		return $org_rel->prop("org");
	}

	function connections_from($arr)
	{
		$rv = parent::connections_from($arr);
		if (($arr["type"] == 67 || $arr["type"] == "RELTYPE_CURRENT_JOB") && count($rv) == 0 && !$GLOBALS["NOC"])
		{
			$o = obj($this->id());
			$work = $o->get_first_obj_by_reltype("RELTYPE_WORK");
			if (!$work)
			{
				return $rv;
			}
			$prof = $o->get_first_obj_by_reltype("RELTYPE_RANK");
			if (!$prof)
			{
				// try to find reverse version
				$try_conns = $this->connections_to(array("from.class_id" => CL_CRM_PROFESSION));
				if (count($try_conns))
				{
					$try_c = reset($try_conns);
					$prof = $try_c->from();
				}
			}
		//	die(dbg::dump($prof));
			// no job relations. create a default
			$crel = obj();
			$crel->set_parent($this->parent());
			$crel->set_class_id(CL_CRM_PERSON_WORK_RELATION);$crel->save();
			$crel->set_prop("org", $work->id());
			if ($prof)
			{
				$crel->set_prop("profession", $prof->id());
			}
			$sect = $o->get_first_obj_by_reltype("RELTYPE_SECTION");
			if (!$sect)
			{
				$try_conns = $this->connections_to(array("from.class_id" => CL_CRM_SECTION));
				if (count($try_conns))
				{
				 	$try_c = reset($try_conns);
					$sect = $try_c->from();
				}
			}
			if ($sect)
			{
				$crel->set_prop("section", $sect->id());
				//$crel->set_prop("section2", $sect->id());
			}
			aw_disable_acl();
			$crel->save();
			$GLOBALS["NOC"] = 1;
			$this->connect(array("to" => $crel->id(), "type" => 67));
			aw_restore_acl();
			$rv = parent::connections_from($arr);
			$GLOBALS["NOC"] = 0;
		}
		return $rv;
	}
}

?>

