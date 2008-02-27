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
		$org_rel = $o->get_first_obj_by_reltype("RELTYPE_CURRENT_JOB");
		if (!$org_rel)
		{
			return false;
		}
		return $org_rel->prop("org");
	}
}

?>
