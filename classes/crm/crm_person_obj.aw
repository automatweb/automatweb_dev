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

	function set_name($v)
	{
		$v = htmlspecialchars($v);
		return parent::set_name($v);
	}

	function set_prop($k, $v)
	{
		$html_allowed = array();
		if(!in_array($k, $html_allowed))
		{
			$v = htmlspecialchars($v);
		}

		switch($k)
		{
			case "rank":
				return $this->set_rank($v);

			case "work_contact":
				return $this->set_work_contact($v);

			case "org_section":
				return $this->set_org_section($v);

			case "fake_email":
				return $this->set_fake_email($v);

			case "fake_phone":
				return $this->set_fake_phone($v);

			case "fake_address_country":
			case "fake_address_county":
			case "fake_address_city":
			case "fake_address_postal_code":
			case "fake_address_address":
			case "fake_address_address2":
				return $this->set_fake_address_prop($k, $v);
		}
		return parent::set_prop($k, $v);
	}

	function prop($k)
	{
		switch($k)
		{
			case "fake_email":
				return parent::prop("email.mail");

			case "fake_phone":
				return parent::prop("phone.name");

			case "fake_address_country":
				return parent::prop("address.riik.name");

			case "fake_address_county":
				return parent::prop("address.maakond.name");

			case "fake_address_city":
				return parent::prop("address.linn.name");

			case "fake_address_postal_code":
				return parent::prop("address.postiindeks");

			case "fake_address_address":
				return parent::prop("address.aadress");

			case "fake_address_address2":
				return parent::prop("address.aadress2");

			case "work_contact":
				return $this->find_work_contact();

			case "rank":
				return $this->get_rank();

			case "org_section":
				return $this->get_org_section();
		}
		if($k == "title" && parent::prop($k) === 0)
		{
			return 3;
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
		enter_function("crm_person_obj::get_applications");
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
		{
			return $ret;
		}

		$conns = connection::find(array(
			"to" => $ids,
			"from.class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
			"type" => "RELTYPE_CANDIDATE"
		));

		$pm = get_instance(CL_PERSONNEL_MANAGEMENT);
		foreach($conns as $conn)
		{
			$from = obj($conn["from"]);
			if((in_array($conn["from.status"], $arr["status"]) || count($arr["status"]) == 0) && (in_array($conn["from.parent"], $arr["parent"]) || count($arr["parent"]) == 0) && $pm->check_special_acl_for_obj($from))
			{
				$ret->add($conn["from"]);
			}
		}
		exit_function("crm_person_obj::get_applications");

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
			$o = $cn->to();
			$o->conn_id = $cn->id();
			$ol->add($o);
		}
		$ids = array();
		foreach(parent::connections_from(array("type" => "RELTYPE_CURRENT_JOB")) as $cn)
		{
			$ids[] = $cn->prop("to");
		}
		if(count($ids) > 0)
		{
			$prms = array("from" => $ids, "type" => "RELTYPE_PHONE", "from.class_id" => CL_CRM_PERSON_WORK_RELATION);
			// You wish! -kaarel
			/*if(isset($type))
			{
				$prms["to.type"] = $type;
			}*/
			foreach(connection::find($prms) as $cn)
			{
				$o = obj($cn["to"]);
				$o->conn_id = $cn["id"];
				$ol->add($o);
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

	function has_tasks()
	{
		$c = new connection();
		$cs = $c->find(array(
			"from" => $this->id(),
			"from.class_id" => CL_CRM_PERSON,
			"type" => "RELTYPE_PERSON_TASK",
		));
		if(sizeof($cs))
		{
			return 1;
		}
		return 0;
	}

	function has_meetings()
	{
		$c = new connection();
		$cs = $c->find(array(
			"from" => $this->id(),
			"from.class_id" => CL_CRM_PERSON,
			"type" => "RELTYPE_PERSON_MEETING",
		));
		if(sizeof($cs))
		{
			return 1;
		}
		return 0;
	}

	function has_calls()
	{
		$c = new connection();
		$cs = $c->find(array(
			"from" => $this->id(),
			"from.class_id" => CL_CRM_PERSON,
			"type" => "RELTYPE_PERSON_CALL",
		));
		if(sizeof($cs))
		{
			return 1;
		}
		return 0;
	}

	function has_ovrv_offers()
	{
		$filt = array(
			"class_id" => CL_CRM_DOCUMENT_ACTION,
			"site_id" => array(),
			"lang_id" => array(),
			"actor" => $this->id(),
		);

		$ol = new object_list($filt);
		if(sizeof($ol->ids()))
		{
			return 1;
		}
		return 0;
	}

	function has_bugs()
	{
		$c = new connection();
		$cs = $c->find(array(
			"to" => $this->id(),
			"from.class_id" => CL_BUG,
			"type" => "RELTYPE_MONITOR",
			"bug_status" => array(1,2,10,11),
		));
		if(sizeof($cs))
		{
			return 1;
		}
		return 0;
	}

	function has_projects()
	{
		$col = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"site_id" => array(),
			"lang_id" => array(),
			"CL_PROJECT.RELTYPE_PARTICIPANT" => $this->id(),
		));
		if($col->count())
		{
			return 1;
		}
		return 0;
	}

	function is_cust_mgr()
	{
		$col = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"site_id" => array(),
			"lang_id" => array(),
			"CL_CRM_COMPANY.client_manager" => $this->id(),
		));
		if($col->count())
		{
			return 1;
		}
		return 0;
	}

	/** sets the default email adress content or creates it if needed **/
	private function set_fake_email($mail)
	{
		$n = false;
		if ($GLOBALS["object_loader"]->cache->can("view", $this->prop("email")))
		{
			$eo = obj($this->prop("email"));
		}
		else
		{
			$eo = obj();
			$eo->set_class_id(CL_ML_MEMBER);
			$eo->set_parent($this->id());
			$eo->set_prop("name", $this->name());
			$n = true;
		}

		$eo->set_prop("mail", $mail);
		$eo->save();

		if ($n)
		{
			$this->set_prop("email", $eo->id());
			$this->save();
			$this->connect(array(
				"type" => "RELTYPE_EMAIL",
				"to" => $eo->id()
			));
		}
	}

	/** sets the default email adress content or creates it if needed **/
	private function set_fake_phone($phone)
	{
		$n = false;
		if ($GLOBALS["object_loader"]->cache->can("view", $this->prop("phone")))
		{
			$eo = obj($this->prop("phone"));
		}
		else
		{
			$eo = obj();
			$eo->set_class_id(CL_CRM_PHONE);
			$eo->set_parent($this->id());
			$n = true;
		}

		$eo->set_name($phone);
		$eo->save();

		if ($n)
		{
			$this->set_prop("phone", $eo->id());
			$this->save();
			$this->connect(array(
				"type" => "RELTYPE_PHONE",
				"to" => $eo->id()
			));
		}
	}

	private function set_fake_address_prop($k, $v)
	{
		$pmap = array(
			"fake_address_country" => "riik",
			"fake_address_county" => "maakond",
			"fake_address_city" => "linn",
			"fake_address_postal_code" => "postiindeks",
			"fake_address_address" => "aadress",
			"fake_address_address2" => "aadress2"
		);
		$n = false;
		if ($GLOBALS["object_loader"]->cache->can("view", $this->prop("address")))
		{
			$eo = obj($this->prop("address"));
		}
		else
		{
			$eo = obj();
			$eo->set_class_id(CL_CRM_ADDRESS);
			$eo->set_parent($this->id());
			$n = true;
		}

		switch($k)
		{
			case "fake_address_county":
			case "fake_address_city":
			case "fake_address_country":
				$this->_adr_set_via_rel($eo, $pmap[$k], $v);
				break;

			case "fake_address_postal_code":
			case "fake_address_address":
			case "fake_address_address2":
				$eo->set_prop($pmap[$k], $v);
				break;
		}

		$eo->save();

		if ($n)
		{
			$this->set_prop("address", $eo->id());
			$this->save();
		}
	}

	private function _adr_set_via_rel($o, $prop, $val)
	{
		if ($GLOBALS["object_loader"]->cache->can("view", $o->prop($prop)))
		{
			$ro = obj($o->prop($prop));
		}
		else
		{
			$pl = $o->get_property_list();
			$rl = $o->get_relinfo();

			$ro = obj();
			$ro->set_class_id($rl[$pl[$prop]["reltype"]]["clid"][0]);
			$ro->set_parent(is_oid($o->id()) ? $o->id() : $o->parent());
		}
		$ro->set_name($val);
		$ro->save();

		$o->set_prop($prop, $ro->id());
	}

	/**
	@attrib name=get_sections api=1
	**/
	function get_sections()
	{
		$ol = new object_list();
		foreach($this->connections_from(array("type" => "RELTYPE_CURRENT_JOB")) as $conn)
		{
			$to = $conn->to();
			if(is_oid($to->section) && $this->can("view", $to->section))
			{
				$ol->add($to->section);
			}
		}
		return $ol;
	}

	/**
	@attrib name=get_companies api=1
	**/
	function get_companies()
	{
		$ol = new object_list();
		foreach($this->connections_from(array("type" => "RELTYPE_CURRENT_JOB")) as $conn)
		{
			$to = $conn->to();
			if(is_oid($to->org) && $this->can("view", $to->org))
			{
				$ol->add($to->org);
			}
		}
		return $ol;
	}

	/** returns person image tag
		@attrib api=1
	**/
	public function get_image_tag()
	{
		$imgo = $this->get_first_obj_by_reltype("RELTYPE_PICTURE");
		$img = "";
		if ($imgo)
		{
			$img_i = $imgo->instance();
			$img = $img_i->make_img_tag_wl($imgo->id(),"","",array("width" => 60));
		}
		return $img;
	}

	/** returns one phone number
		@attrib api=1
	**/
	public function get_phone($co = null , $sect = null)
	{
		foreach(parent::connections_from(array("type" => "RELTYPE_CURRENT_JOB")) as $cn)
		{
			$current_job = $cn->to();
			if($co && $current_job->prop("org") != $co)
			{
				continue;
			}

			if($sect && $current_job->prop("section") != $sect)
			{
				continue;
			}

			$phone = $current_job->get_first_obj_by_reltype("RELTYPE_PHONE");
			if(is_object($phone))
			{
				return $phone->name();
			}
		}

		foreach(parent::connections_from(array("type" => "RELTYPE_PHONE")) as $cn)
		{
			return $cn->prop("to.name");
		}
		return "";
	}

	/** returns one e-mail address
		@attrib api=1
	**/
	public function get_mail($co = null , $sect = null)
	{
		foreach(parent::connections_from(array("type" => "RELTYPE_CURRENT_JOB")) as $cn)
		{
			$current_job = $cn->to();
			if($co && $current_job->prop("org") != $co)
			{
				continue;
			}

			if($sect && $current_job->prop("section") != $sect)
			{
				continue;
			}

			$mail = $current_job->get_first_obj_by_reltype("RELTYPE_MAIL");
			if(is_object($mail))
			{
				return $mail->prop("mail");
			}
		}

		foreach(parent::connections_from(array("type" => "RELTYPE_EMAIL")) as $cn)
		{
			$mail = $cn->to();
			return $mail->prop("mail");
		}
		return "";
	}

	/** returns one e-mail address link
		@attrib api=1
	**/
	public function get_mail_tag($co = null , $sect = null)
	{
		$mail = $this->get_mail($co , $sect);
		if(is_email($mail))
		{
			return html::href(array(
					"url" => "mailto:" . $mail,
					"caption" => $mail
				));
		}
		return "";
	}

	/** returns all e-mail address links
		@attrib api=1
	**/
	public function get_all_mail_tags($co = null , $sect = null)
	{
		$mails = $this->emails();
		$ret = array();
		foreach($mails->arr() as $mail)
		{
			if(is_email($mail->prop("mail")))
			{
				$ret[]= html::href(array(
					"url" => "mailto:" . $mail->prop("mail"),
					"caption" => $mail->prop("mail")
				));
			}
		}
		return $ret;
	}

	/** return all current work relation orgs
		@attrib api=1
		@return object list
	**/
	public function get_all_orgs()
	{
		$ol = new object_list();
		$this->set_current_jobs();
		foreach($this->current_jobs->arr() as $conn)
		{
			if($conn->prop("org"))
			{
				$ol->add($conn->prop("org"));
			}
		}
		return $ol;
	}

	

	/** returns all current job relations
		@attrib api=1
	**/
	public function set_current_jobs()
	{
		if(!$this->current_jobs)
		{
			$this->current_jobs = new object_list();
			foreach(parent::connections_from(array("type" => "RELTYPE_CURRENT_JOB")) as $cn)
			{
				$this->current_jobs->add($cn->prop("to"));
			}
		}
	}

	/** returns person section names
		@attrib api=1
		@param co optional type=oid
			company id
		@return array
			section names
	**/
	public function get_section_names($co)
	{
		$this->set_current_jobs();
		$sections = array();
		foreach($this->current_jobs->arr() as $o)
		{
			if((!$co || $co == $o->prop("org")) && $o->prop("section.name"))
			{
				$sections[] = $o->prop("section.name");
			}
		}
		return $sections;
	}

	/** returns person profession names
		@attrib api=1
		@param co optional type=oid
			company id
		@return array
			profession names
	**/
	public function get_profession_names($co)
	{
		$this->set_current_jobs();
		$sections = array();
		foreach($this->current_jobs->arr() as $o)
		{
			if((!$co || $co == $o->prop("org")) && $o->prop("profession.name"))
			{
				$sections[] = $o->prop("profession.name");
			}
		}
		return $sections;
	}

	/** adds new work relation
		@attrib api=1 params=name
		@param org optional type=oid default=current company id
			Company id
		@param section optional type=oid
			Section id
		@param profession optional type=oid
			Profession id
		@param room optional type=oid
		@param start optional type=int default=time()
			Work relation start timestamp
		@param end optional type=int
			Work relation start timestamp
		@param tasks optional type=sting
			Work tasks
		@param salary optional type=int
			Work salary per month
		@param salary_currency optional type=int
			Work salary currency
		@return oid
			Work relation object id
	**/
	public function add_work_relation($arr = array())
	{
		$wr = new object();
		$wr->set_parent($this->id());
		$wr->set_name($this->name() . " ".t("work relation"));
		$wr->set_class_id(CL_CRM_PERSON_WORK_RELATION);
		if(!$arr["org"])
		{
			$company = get_current_company();
			$arr["org"] = $company->id();
		}
		if(!$arr["start"])
		{
			$arr["start"] = time();
		}
		foreach($arr as $key => $val)
		{
			if($wr->is_property($key))
			{
				$wr->set_prop($key , $val);
			}
		}
		$wr->save();

		$this->connect(array(
			"to" => $wr->id(),
			"reltype" => "RELTYPE_CURRENT_JOB",
		));

		return $wr->id();
	}
}

?>
