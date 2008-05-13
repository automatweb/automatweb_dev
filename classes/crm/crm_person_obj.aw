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

	function set_rank($v)
	{
		// It won't work with new object, so we need to save it first.
		if(!is_oid($this->id()))
		{
			$this->save();
		}

		$o = obj($this->id());
		$org_rel = $o->get_first_obj_by_reltype("RELTYPE_CURRENT_JOB");
		if (!$org_rel)
		{
			$org_rel = obj();
			$org_rel->set_class_id(CL_CRM_PERSON_WORK_RELATION);
			$org_rel->set_parent($o->id());
			$org_rel->save();
			$o->connect(array(
				"to" => $org_rel->id(),
				"type" => "RELTYPE_CURRENT_JOB",
			));
		}
		$sp = $org_rel->set_prop("profession", $v);
		$org_rel->save();
		return $sp;
	}

	function get_rank()
	{
		// It won't work with new object, so we need to check the oid.
		if(!is_oid($this->id()))
			return false;

		$o = obj($this->id());
		$org_rel = $o->get_first_obj_by_reltype("RELTYPE_CURRENT_JOB");
		if (!$org_rel)
		{
			return false;
		}
		return $org_rel->prop("profession");
	}

	function set_prop($k, $v)
	{
		if($k == "rank")
		{
			return $this->set_rank($v);
		}
		if($k == "work_contact")
		{
			return $this->set_work_contact($v);
		}
		if($k == "org_section")
		{
			return $this->set_org_section($v);
		}
		return parent::set_prop($k, $v);
	}

	function prop($k)
	{
		if ($k == "work_contact")
		{
			return $this->find_work_contact();
		}
		if($k == "rank")
		{
			return $this->get_rank();
		}
		if($k == "org_section")
		{
			return $this->get_org_section();
		}
		return parent::prop($k);
	}

	function find_work_contact()
	{
		// It won't work with new object, so we need to check the oid.
		if(!is_oid($this->id()))
			return false;

		$o = obj($this->id());
		$org_rel = $o->get_first_obj_by_reltype("RELTYPE_CURRENT_JOB");
		if (!$org_rel)
		{
			return false;
		}
		return $org_rel->prop("org");
	}

	function set_work_contact($v)
	{
		// It won't work with new object, so we need to save it first.
		if(!is_oid($this->id()))
		{
			$this->save();
		}

		$o = obj($this->id());
		$org_rel = $o->get_first_obj_by_reltype("RELTYPE_CURRENT_JOB");
		if (!$org_rel)
		{
			$org_rel = obj();
			$org_rel->set_class_id(CL_CRM_PERSON_WORK_RELATION);
			$org_rel->set_parent($o->id());
			$org_rel->save();
			$o->connect(array(
				"to" => $org_rel->id(),
				"type" => "RELTYPE_CURRENT_JOB",
			));
		}
		$sp = $org_rel->set_prop("org", $v);
		$org_rel->save();
		return $sp;
	}

	function set_org_section($v)
	{
		// It won't work with new object, so we need to save it first.
		if(!is_oid($this->id()))
		{
			$this->save();
		}

		$o = obj($this->id());
		$org_rel = $o->get_first_obj_by_reltype("RELTYPE_CURRENT_JOB");
		if (!$org_rel)
		{
			$org_rel = obj();
			$org_rel->set_class_id(CL_CRM_PERSON_WORK_RELATION);
			$org_rel->set_parent($o->id());
			$org_rel->save();
			$o->connect(array(
				"to" => $org_rel->id(),
				"type" => "RELTYPE_CURRENT_JOB",
			));
		}
		$sp = $org_rel->set_prop("section", $v);
		$org_rel->save();
		return $sp;
	}

	function get_org_section()
	{
		// It won't work with new object, so we need to check the oid.
		if(!is_oid($this->id()))
			return false;

		$o = obj($this->id());
		$org_rel = $o->get_first_obj_by_reltype("RELTYPE_CURRENT_JOB");
		if (!$org_rel)
		{
			return false;
		}
		return $org_rel->prop("section");
	}

	function add_person_to_list($arr)
	{
		$o = obj($arr["id"]);
		$o->connect(array(
			"to" => $arr["list_id"],
			"reltype" => "RELTYPE_CATEGORY",
		));
	}

	function get_applications($arr = array())
	{
		$this->prms(&$arr);

		/*
		// Gimme a reason why this won't work!?
		return new object_list(array(
			"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
			"parent" => $arr["parent"],
			"status" => $arr["status"],
			"CL_PERSONNEL_MANAGEMENT_JOB_OFFER.RELTYPE_CANDIDATE.RELTYPE_PERSON" => parent::id(),
		));
		*/
		$ret = new object_list();

		$conns = connection::find(array(
			"to" => parent::id(),
			"from.class_id" => CL_PERSONNEL_MANAGEMENT_CANDIDATE,
			"type" => "RELTYPE_PERSON"
		));
		foreach($conns as $conn)
		{
			$ids[] = $conn["from"];
		}

		if(count($ids) == 0)
			return $ret;

		$conns = connection::find(array(
			"to" => $ids,
			"from.class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
			"type" => "RELTYPE_CANDIDATE"
		));
		foreach($conns as $conn)
		{
			if((in_array($conn["from.status"], $arr["status"]) || count($arr["status"]) == 0) && (in_array($conn["from.parent"], $arr["parent"]) || count($arr["parent"]) == 0))
			{
				$ret->add($conn["from"]);
			}
		}

		return $ret;
	}
	
	function prms($arr)
	{
		$arr["parent"] = !isset($arr["parent"]) ? array() : $arr["parent"];
		if(!is_array($arr["parent"]))
		{
			$arr["parent"] = array($arr["parent"]);
		}
		$arr["status"] = !isset($arr["status"]) ? array() : $arr["status"];
		if(!is_array($arr["status"]))
		{
			$arr["status"] = array($arr["status"]);
		}
		$arr["childs"] = !isset($arr["childs"]) ? true : $arr["childs"];

		if($arr["childs"] && (!is_array($arr["parent"]) || count($arr["parent"]) > 0))
		{
			$pars = $arr["parent"];
			foreach($pars as $par)
			{
				$ot = new object_tree(array(
					"class_id" => CL_MENU,
					"status" => $arr["status"],
					"parent" => $par,
				));
				foreach($ot->ids() as $oid)
				{
					$arr["parent"][] = $oid;
				}
			}
		}
	}

	function get_age()
	{
		// Implement better syntaxt check here!
		if(strlen(parent::prop("birthday")) != 10)
			return false;

		$date_bits = explode("-", parent::prop("birthday"));
		$age = date("Y") - $date_bits[0];
		if(date("m") < $date_bits[1] || date("m") == $date_bits[1] && date("d") < $date_bits[2])
		{
			$age--;
		}

		return ($age < 0) ? false : $age;
	}

	function phones($type = NULL)
	{
		$ol = new object_list;
		$prms = array("type" => "RELTYPE_PHONE");
		// You wish! -kaarel
		/*if(isset($type))
		{
			$prms["to.type"] = $type;
		}*/
		foreach(parent::connections_from($prms) as $cn)
		{
			$ol->add($cn->prop("to"));
		}
		$ids = array();
		foreach(parent::connections_from(array("type" => "RELTYPE_CURRENT_JOB")) as $cn)
		{
			$ids[] = $cn->prop("to");
		}
		if(count($ids) > 0)
		{			
			$prms = array("from" => $ids, "type" => "RELTYPE_PHONE");
			// You wish! -kaarel
			/*if(isset($type))
			{
				$prms["to.type"] = $type;
			}*/
			foreach(connection::find($prms) as $cn)
			{
				$ol->add($cn["to"]);
			}
		}
		if(isset($type))
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_PHONE,
				"oid" => $ol->ids(),
				"type" => $type,
				"status" => array(),
				"parent" => array(),
				"site_id" => array(),
				"lang_id" => array(),
			));
		}
		return $ol;
	}

	function emails()
	{
		$ol = new object_list;
		foreach(parent::connections_from(array("type" => "RELTYPE_EMAIL")) as $cn)
		{
			$ol->add($cn->prop("to"));
		}
		$ids = array();
		foreach(parent::connections_from(array("type" => "RELTYPE_CURRENT_JOB")) as $cn)
		{
			$ids[] = $cn->prop("to");
		}
		if(count($ids) > 0)
		{
			foreach(connection::find(array("from" => $ids, "type" => "RELTYPE_EMAIL")) as $cn)
			{
				$ol->add($cn["to"]);
			}
		}
		return $ol;
	}

	function get_skills()
	{
		$ol = new object_list();
		foreach($this->connections_from(array("type" => "RELTYPE_HAS_SKILL")) as $conn)
		{
			$ol ->add($conn->prop("to"));
		}
		return $ol;
	}

	function get_skill_names()
	{
		$ol = new object_list();
		foreach($this->connections_from(array("type" => "RELTYPE_HAS_SKILL")) as $conn)
		{
			$ol ->add($conn->prop("to"));
		}
		$ret = array();
		foreach($ol->arr() as $o)
		{
			$ret[$o->id()] = $o->prop("skill.name");
		}
		return $ret;
	}
}

?>
