<?php
class crm_person_work_relation_fix extends _int_object
{
	function set_prop($var, $val)
	{
		$html_allowed = array();
		if(!in_array($var, $html_allowed) && !is_array($val))
		{
			$val = htmlspecialchars($val);
		}

		if($var == "org" && is_oid($this->id()))
		{
			foreach(parent::connections_from(array("type" => 1)) as $conn)
			{
				if($conn->prop("to") != $val)
				{
					$conn->delete();
				}
			}
		}
		if($var == "section" && is_oid($this->id()))
		{
			foreach(parent::connections_from(array("type" => 7)) as $conn)
			{
				if($conn->prop("to") != $val)
				{
					$conn->delete();
				}
			}
		}
		if($var == "profession" && is_oid($this->id()))
		{
			foreach(parent::connections_from(array("type" => 3)) as $conn)
			{
				if($conn->prop("to") != $val)
				{
					$conn->delete();
				}
			}
		}
		if($var == "section2")
		{
			return $this->set_prop("section", $val);
		}
		return parent::set_prop($var, $val);
	}

	function prop($v)
	{
		if($v == "section2")
		{
			return parent::prop("section");
		}
		return parent::prop($v);
	}

	/** sets profession to work relation
		@attrib api=1 params=pos
		@param profession required type=oid/string
		@param parent optional default=NULL
		@returns oid
			profession object id
	**/
	public function set_profession($profession,$parent = null)
	{
		if(!$parent)
		{
			$parent = $this->id();
		}
		if(is_oid($profession) && $GLOBALS["object_loader"]->can("view" , $profession))
		{
			$id = $profession;
		}
		else
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_PROFESSION,
				"lang_id" => array(),
				"site_id" => array(),
				"name" => $profession,
			));
			$id = reset($ol->ids());
			if(!$id)
			{
				$o = new object();
				$o->set_parent($parent);
				$o->set_class_id(CL_CRM_PROFESSION);
				$o->set_name($profession);
				$o->save();
				$id = $o->id();
			}
		}
		$this->set_prop("profession" , $id);
		$this->save();
		return $id;
	}

	/** sets section to work relation
		@attrib api=1 params=pos
		@param section required type=oid/string
		@returns oid
			section object id
	**/
	public function set_section($section)
	{
		$parent = $this->prop("org");
		if(!$parent)
		{
			$parent = $this->parent();
		}
		if(is_oid($section) && $GLOBALS["object_loader"]->can("view" , $section))
		{
			$id = $section;
		}
		else
		{
			if($GLOBALS["object_loader"]->can("view" , $this->prop("org")))
			{
				$org = obj($this->prop("org"));
				$sections = $org->get_sections();
				$section_names = $sections->names();
				foreach($section_names as $key => $nm)
				{
					if($nm == $section)
					{
						$id = $key;
						break;
					}
				}
			}

			if(!$id)
			{
				$o = new object();
				$o->set_parent($parent);
				$o->set_class_id(CL_CRM_SECTION);
				$o->set_name($section);
				$o->save();
				$id = $o->id();
				if($org)
				{
					$org->connect(array("to" =>$o->id(), "type" => "RELTYPE_SECTION"));
				}
			}
		}

		$this->set_prop("section" , $id);
		$this->save();

		return $id;
	}

	/** sets mail address to work relation
		@attrib api=1 params=pos
		@param mail required type=string
	**/
	public function set_mail($mail)
	{
		$o = new object();
		$o->set_parent($this->id());
		$o->set_class_id(CL_ML_MEMBER);
		$o->set_name($mail);
		$o->set_prop("mail" , $mail);
		$o->save();

		$conns = $this->connections_from(array("type" => "RELTYPE_EMAIL"));
		foreach($conns as $conn)
		{
			$conn->delete();
		}
		$this->connect(array("to" =>$o->id(), "type" => "RELTYPE_EMAIL"));
		return $o->id();
	}

	/** sets phone to work relation
		@attrib api=1 params=pos
		@param phone required type=string
	**/
	public function set_phone($phone)
	{
		$o = new object();
		$o->set_parent($this->id());
		$o->set_class_id(CL_CRM_PHONE);
		$o->set_name($phone);
		$o->save();

		$conns = $this->connections_from(array("type" => "RELTYPE_PHONE"));
		foreach($conns as $conn)
		{
			$conn->delete();
		}
		//mis kuradi jama see on - m6nikord ei saa just tehtud objekti id'd k2tte
		if($o->id())
		{
			$this->connect(array("to" =>$o->id(), "type" => "RELTYPE_PHONE"));
		}
		return $o->id();
	}

	/** finishes current work relation
		@attrib api=1
	**/
	public function finish()
	{
		$person = $this->get_person();
		if(!is_object($person))
		{
			return false;
		}
		$person->disconnect(array(
			"from" => $this->id(),
		));
		$person->connect(array(
			"to" => $this->id(),
			"type" => "RELTYPE_PREVIOUS_JOB",
		));
		$this->set_prop("end" , time());
		$this->save();
		return $this->id();
	}

	private function get_person()
	{
		$persons  = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"lang_id" => array(),
			"site_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_ORG_RELATION" => $this->id(),
					"CL_CRM_PERSON.RELTYPE_PREVIOUS_JOB" => $this->id(),
					"CL_CRM_PERSON.RELTYPE_CURRENT_JOB" => $this->id(),
				)
			)),
		));
		return reset($persons->arr());
	}
}
?>
