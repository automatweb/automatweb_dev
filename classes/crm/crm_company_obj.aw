<?php

class crm_company_obj extends _int_object
{
	function prop($k)
	{
		if($k == "show_on_web")
		{
			$org = get_instance(CL_USER)->get_current_company();
			if(!is_oid($org) || !is_oid($this->id()))
			{
				return false;
			}
			$ol = new object_list(array(
				"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
				"seller" => $org,
				"buyer" => $this->id(),
				"lang_id" => array(),
				"site_id" => array(),
				"show_in_webview" => 1,
				"limit" => 1,
			));
			return $ol->count() > 0;
		}

		if(substr($k, 0, 5) == "fake_" && is_oid($this->id()))
		{
			switch(substr($k, 5))
			{
				case "url":
					return is_oid(parent::prop("url_id")) ? parent::prop("url_id.url") : parent::prop("RELTYPE_URL.url");

				case "email":
					return is_oid(parent::prop("email_id")) ? parent::prop("email_id.mail") : parent::prop("RELTYPE_EMAIL.mail");

				case "phone":
					return is_oid(parent::prop("phone_id")) ? parent::prop("phone_id.name") : parent::prop("RELTYPE_PHONE.name");

				case "address_country":
					return is_oid(parent::prop("contact")) ? parent::prop("contact.riik.name") : parent::prop("RELTYPE_ADDRESS.riik.name");

				case "address_country_relp":
					return is_oid(parent::prop("contact")) ? parent::prop("contact.riik") : parent::prop("RELTYPE_ADDRESS.riik");

				case "address_county":
					return is_oid(parent::prop("contact")) ? parent::prop("contact.maakond.name") : parent::prop("RELTYPE_ADDRESS.maakond.name");

				case "address_county_relp":
					return is_oid(parent::prop("contact")) ? parent::prop("contact.maakond") : parent::prop("RELTYPE_ADDRESS.maakond");

				case "address_city":
					return is_oid(parent::prop("contact")) ? parent::prop("contact.linn.name") : parent::prop("RELTYPE_ADDRESS.linn.name");

				case "address_city_relp":
					return is_oid(parent::prop("contact")) ? parent::prop("contact.linn") : parent::prop("RELTYPE_ADDRESS.linn");

				case "address_postal_code":
					return is_oid(parent::prop("contact")) ? parent::prop("contact.postiindeks") : parent::prop("RELTYPE_ADDRESS.postiindeks");

				case "address_address":
					return is_oid(parent::prop("contact")) ? parent::prop("contact.aadress") : parent::prop("RELTYPE_ADDRESS.aadress");

				case "address_address2":
					return is_oid(parent::prop("contact")) ? parent::prop("contact.aadress2") : parent::prop("RELTYPE_ADDRESS.aadress2");
			}
		}
		return parent::prop($k);
	}

	function set_prop($name,$value)
	{
		if($k == "show_on_web")
		{
			$org = get_instance(CL_USER)->get_current_company();
			if(is_oid($org) && is_oid($this->id()))
			{
			}
			$ol = new object_list(array(
				"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
				"seller" => $org,
				"buyer" => $this->id(),
				"lang_id" => array(),
				"site_id" => array(),
				"show_in_webview" => 1,
				"limit" => 1,
			));
			if($ol->count() > 0)
			{
				$o = $ol->begin();
				$o->show_in_webview = $value;
				$o->save();
			}
			elseif($value)
			{
				$o = obj();
				$o->set_parent($org);
				$o->set_class_id(CL_CRM_COMPANY_CUSTOMER_DATA);
				$o->buyer = $this->id();
				$o->seller = $org;
				$o->show_in_webview = $value;
				$o->save();
			}
		}
		if($name == "name")
		{
			$value = htmlspecialchars($value);
		}
		if(substr($name, 0, 5) == "fake_")
		{
			switch(substr($name, 5))
			{
				case "url":
					return $this->set_fake_url($value);

				case "email":
					return $this->set_fake_email($value);

				case "phone":
					return $this->set_fake_phone($value);

				case "address_country":
				case "address_country_relp":
				case "address_county":
				case "address_county_relp":
				case "address_city":
				case "address_city_relp":
				case "address_postal_code":
				case "address_address":
				case "address_address2":
					return $this->set_fake_address_prop($name, $value);
			}
		}
		parent::set_prop($name,$value);
	}

	function set_name($v)
	{
		$v = htmlspecialchars($v);
		return parent::set_name($v);
	}

	function get_undone_orders()
	{
		$filter = array(
			"class_id" => CL_SHOP_ORDER,
			"orderer_company" => $this->id(),
//			"CL_CHOP_ORDER.order_completed" => 1,
			"site_id" => array(),
			"lang_id" => array(),
		);
		$ol = new object_list($filter);
//see ei ole hea, et peab kindlasti ymber tegema, kuid va toodet on igalpool kasutuses, et ei taha hetkel selle muutmisele m6elda
		foreach($ol->arr() as $o)
		{
			if($o->meta("order_completed"))
			{
				$ol->remove($o->id());
			}
		}
		return $ol;
	}

	function get_unpaid_bills()
	{
		$filter = array(
			"class_id" => CL_CRM_BILL,
			"customer" => $this->id(),
			"state" => new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, 1, 2),
			"site_id" => array(),
			"lang_id" => array(),
		);
		$ol = new object_list($filter);
		return $ol;
	}

	function get_cash_flow($start , $end)
	{
		$filter = array(
			"class_id" => CL_CRM_BILL,
			"customer" => $this->id(),
//			"state" => 2,
			"site_id" => array(),
			"lang_id" => array(),
		);

		if(!$start)
		{
			$start = mktime(0, 0, 0, 0, 0, (date("Y", time())) - 1);
		}

		if ($end > 100)
		{
			$filter["bill_date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $start, $end);
		}
		else
		{
			$filter["bill_date"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $start);
		}

		$ol = new object_list($filter);
//		return $ol;

		$bill_i = get_instance(CL_CRM_BILL);
		$co_stat_inst = get_instance("applications/crm/crm_company_stats_impl");
		$company_curr = $co_stat_inst->get_company_currency();

		foreach($ol->arr() as $bill)
		{
			$cursum = $bill_i->get_bill_sum($bill,$tax_add);

			//paneme ikka oma valuutasse ymber asja
			$curid = $bill->prop("customer.currency");
			if($company_curr && $curid && ($company_curr != $curid))
			{
				$cursum  = $this->convert_to_company_currency(array(
					"sum" =>  $cursum,
					"o" => $bill,
				));
			}
			$sum+= $cursum;
		}

		return number_format($sum , 2);
	}
	
	// Since the crm_company object is sometimes handled as school...
	function get_students($arr)
	{
		$ret = new object_list;

		// Student is connected to the school via education object.
		$cs = connection::find(array(
			"from" => array(),
			"to" => $arr["id"],
			"type" => "RELTYPE_SCHOOL",
			"from.class_id" => CL_CRM_PERSON_EDUCATION,
		));
		if(count($cs) > 0)
		{
			$schids = array();
			foreach($cs as $c)
			{
				$schids[] = $c["from"];
			}
			$cs = connection::find(array(
				"from" => array(),
				"to" => $schids,
				"from.class_id" => CL_CRM_PERSON,
			));
			foreach($cs as $c)
			{
				$ret->add($c["from"]);
			}
		}

		return $ret;
	}

	function get_educations($arr)
	{
		$ids = isset($arr["id"]) ? $arr["id"] : parent::id();
		$prms = array(
			"class_id" => CL_CRM_PERSON_EDUCATION,
			"lang_id" => array(),
			"site_id" => array(),
			"CL_CRM_PERSON_EDUCATION.RELTYPE_SCHOOL" => $ids
		);
		$prms = array_merge($prms, $arr["prms"]);
		$props = is_array($arr["props"]) ? $arr["props"] : array(
			CL_CRM_PERSON_EDUCATION => array("oid", "name"),
		);

		if($arr["return_as_odl"])
		{
			$r = new object_data_list(
				$prms,
				$props
			);
		}
		else
		{
			$r = new object_list($prms);
		}
		return $r;
	}

	function get_job_offers()
	{
		$r = new object_list;
		foreach($this->connections_to(array("from.class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER, "type" => "RELTYPE_ORG")) as $conn)
		{
			$r->add($conn->prop("from"));
		}
		return $r;
	}

	//vana versioon.... ei soovita kasutada
	function get_employees()
	{
		$ol = new object_list();
		//getting all the workers for the $obj
		$conns = $this->connections_from(array(
			"type" => "RELTYPE_WORKERS",
		));
		foreach($conns as $conn)
		{
			$ol->add($conn->prop('to'));
		}

		//getting all the sections
		$conns = $this->connections_from(array(
			"type" => "RELTYPE_SECTION",
		));
		foreach($conns as $conn)
		{
			$section = $conn->to();
			foreach($section->get_employees()->ids() as $oid)
			{
				$ol->add($oid);
			}
		}

		return $ol;
	}

	/** Returns company work relations
		@attrib api=1 params=name
		@param active optional type=bool
			if set, returns only active workers
		@param profession optional type=oid
			worker profession in company
		@param section optional type=oid
			worker section in company
		@return object list
			person work relations object list
	**/
	public function get_work_relations($arr = array())
	{
		$filter = array(
			"class_id" => CL_CRM_PERSON_WORK_RELATION,
			"lang_id" => array(),
			"site_id" => array(),
			"org" => $this->id(),
		);
		if($arr["active"])
		{
			$filter["start"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, time());
			$filter["end"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, time());
			$filter[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"end" => new obj_predicate_compare(OBJ_COMP_LESS, 1),
					"end" => new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, time()),
				)
			));
		}
		if($arr["profession"])
		{
			$filter["profession"] = $arr["profession"];
		}
		if($arr["section"])
		{
			$filter["section"] = $arr["section"];
		}
		$ol = new object_list($filter);
		return $ol;
	}

//2kki optimeerib... seep2rast selline lisa funktsioon
	/** Returns company worker selection
		@attrib api=1 params=name
		@param active optional type=bool
			if set, returns only active workers
		@return object list
			person object list
	**/
	public function get_worker_selection($arr = array())
	{
		$workers = $this->get_workers($arr);

		return $workers->names();

	}

	/** Returns company workers
		@attrib api=1 params=name
		@param active optional type=bool
			if set, returns only active workers
		@param profession optional type=oid
			worker profession in company
		@param section optional type=oid
			worker section in company
		@return object list
			person object list
	**/
	public function get_workers($arr = array())
	{
		$rels = $this->get_work_relations($arr);
		if(!sizeof($rels->ids()))
		{
			$ol =  new object_list();
		}
		else
		{
			$filter = array(
				//"class_id" => CL_CRM_PERSON_WORK_RELATION,
				"class_id" => CL_CRM_PERSON,
				"lang_id" => array(),
				"site_id" => array(),
				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array(
						"CL_CRM_PERSON.RELTYPE_ORG_RELATION" => $rels->ids(),
						"CL_CRM_PERSON.RELTYPE_PREVIOUS_JOB" => $rels->ids(),
						"CL_CRM_PERSON.RELTYPE_CURRENT_JOB" => $rels->ids(),
					)
				)),
			);
			$ol = new object_list($filter);
		}
		if(!$this->prop("use_only_wr_workers"))
		{
		//vana versioon peab j22ma ka toimima siiski, kui tahetakse k6iki t88tajaid
//		if(!sizeof($arr))
//		{
			$ol->add($this->get_employees());
//		}
		}
		return $ol;

	}

	/** Adds worker to company
		@attrib api=1 params=name
		@param worker required type=string/oid
			person name or object id
		@param profession optional type=string/oid
			worker profession in company
		@param section optional type=string/oid
			worker section in company
		@param mail optional type=string
			work e-mail address
		@param phone optional type=string
			work phone
		@param parent optional type=oid
			new person parent
		@return 
	**/
	public function add_worker_data($arr = array())
	{
		extract($arr);
		if(!$parent)
		{
			$parent = $this->id();
		}
		if(is_oid($worker) && $GLOBALS["object_loader"]->can("view" , $worker))
		{
			$worker = obj($worker);
		}
		else
		{//tegelikult oleks vaja isikukoodi ka vist.. mille j2rgi otsida, et kas tegelt see inimene kuskil 2kki
			$wn = $worker;
			if(!($worker = $this->get_worker_by_name($worker)))
			{
				$worker = new object();
				$worker->set_class_id(CL_CRM_PERSON);
				$worker->set_name($wn);
				$worker->set_parent($parent);
				$worker->save();
			}
		}

		if(!($wrid = $worker->get_work_relation_id(array(
			"company" => $this->id(),
		))))
		{
			$wrid = $worker->add_work_relation(array(
				"org" => $this->id(),
			));
		}

		$wr = obj($wrid);
		
		if($profession)
		{
			$wr->set_profession($profession);
		}
		if($section)
		{
			$wr->set_section($section);
		}

		if($mail)
		{
			$wr->set_mail($mail);
		}

		if($phone)
		{
			$wr->set_phone($phone);
		}
		return $worker->id();
	}

	private function get_worker_by_name($name)
	{
		$sel = $this->get_worker_selection();
		foreach($sel as $id => $n)
		{
			if($n == $name)
			{
				return obj($id);
			}
		}
		return 0;
	}

	/** Adds new eployee
		@attrib api=1 params=name
		@param name optional type=string
			person name
		@param id optional
		@return oid
			person object id
	**/
	function add_employees($data = array())
	{
		if(is_oid($data["id"]))
		{
			$o = obj($data["id"]);
		}
		else
		{
			$o = new object();
			$o->set_name($data["name"] ? $data["name"] : t("(Nimetu)"));
			$o->set_class_id(CL_CRM_PERSON);
			$o->set_parent($this->id());
			$o->save();
		}

		$o->add_work_relation(array("org" => $this->id()));
		return $o->id();
	}

	function get_root_sections()
	{
		$r = new object_list();

		foreach($this->connections_from(array(
			"type" => "RELTYPE_SECTION",
			"sort_by_num" => "to.jrk"
		)) as $conn)
		{
			$r->add($conn->prop("to"));
		}
		return $r;
	}

	function get_sections()
	{
		$r = new object_list();
		foreach($this->connections_from(array("type" => "RELTYPE_SECTION")) as $conn)
		{
			$parent = $conn->to();
			$r->add($parent->get_sections());
		}
		return $r;
	}

	/** Returns a list of task stats types for the company
		@attrib api=1 params=pos

		@returns
			array { type_id => type_desc }

	**/
	function get_activity_stats_types()
	{
		$ol = new object_list($this->connections_from(array("type" => "RELTYPE_ACTIVITY_STATS_TYPE")));
		return $ol->names();
	}

	/** Returns object list of comments for the company.
	@attrib name=get_comments api=1 params=name
	**/
	function get_comments()
	{
		$ol = new object_list();
		$conns = $this->connections_from(array("type" => "RELTYPE_COMMENT"));
		foreach($conns as $conn)
		{
			$ol->add($conn->prop("to"));
		}
		return $ol;
	}

	function get_mail()
	{
		$inst = $this->instance();
		if($inst->can("view" , $this->prop("email_id")))
		{
			$mail = obj($this->prop("email_id"));
		}
		else
		{
			$mail = $this->get_first_obj_by_reltype("RELTYPE_EMAIL");
		}

		if(is_object($mail))
		{
			return $mail->prop("mail");
		}

		return "";
	}

	function get_mails($arr = array("return_as_names" => true))
	{
		extract($arr);
		// $type, $id, $return_as_odl, $return_as_names

		$prms = array(
			"class_id" => CL_ML_MEMBER,
			"status" => array(),
			"parent" => array(),
			"site_id" => array(),
			"lang_id" => array(),
			"CL_ML_MEMBER.RELTYPE_EMAIL(CL_CRM_COMPANY)" => isset($id) ? $id : parent::id(),
		);

		if(isset($return_as_names) && $return_as_names)
		{
			$ret = array();
			$conns = $this->connections_from(array("type" => "RELTYPE_EMAIL"));
			foreach($conns as $conn)
			{
				$ret[]= $conn->prop("to.name");
			}
		}
		elseif(isset($return_as_odl) && $return_as_odl)
		{
			$ret = new object_data_list($prms, array(
				CL_ML_MEMBER => array("oid", "mail"),
			));
		}
		else
		{
			$ret = new object_list($prms);
		}
		return $ret;
	}

	public function add_mail($address)
	{
		$mo = new object();
		$mo->set_class_id(CL_ML_MEMBER);
		$mo->set_parent($this->id());
		$mo->set_name($address);
		$mo->set_prop("mail" , $address);
		$mo->save();

		$conns = $this->connections_from(array("type" => "RELTYPE_EMAIL"));
		if(!sizeof($conns))
		{
			$this->set_prop("email_id" , $mo->id());
			$this->save();
		}
		if(is_oid($mo->id()))//mul pole hetkel 6rna aimugi miks see m6nikord tyhja tulemuse annab
		{
			$this->connect(array("to" =>$mo->id(),  "type" => "RELTYPE_EMAIL"));
		}
		return $mo->id();
	}

	public function change_mail($address)
	{
		$conns = $this->connections_from(array("type" => "RELTYPE_EMAIL"));
		foreach($conns as $conn)
		{
			$conn->delete();
		}
		$mid = $this->add_mail($address);
		return $mid;
	}


	public function add_phone($phone)
	{
		$mo = new object();
		$mo->set_class_id(CL_CRM_PHONE);
		$mo->set_parent($this->id());
		$mo->set_name($phone);
		$mo->save();

		$conns = $this->connections_from(array("type" => "RELTYPE_PHONE"));
		if(!sizeof($conns))
		{
			$this->set_prop("phone_id" , $mo->id());
			$this->save();
		}

		$this->connect(array("to" =>$mo->id(),  "type" => "RELTYPE_PHONE"));
		return $mo->id();
	}

	public function change_phone($phone)
	{
		$conns = $this->connections_from(array("type" => "RELTYPE_PHONE"));
		foreach($conns as $conn)
		{
			$conn->delete();
		}
		$mid = $this->add_phone($phone);
		return $mid;
	}


	/** Adds sector
		@attrib api=1 params=name
		@param id optional type=oid
			sector object id
		@param name optional type=string
			sector name
		@param code optional type=string
			sector code
		@param parent optional type=oid
			parent for new sector object
		@return oid
			sector object id
	**/
	public function add_sector($arr)
	{
		$filter = array();
		$filter["class_id"] = CL_CRM_SECTOR;
		$filter["lang_id"] = array();
		$filter["site_id"] = array();
		if($arr["id"])
		{
			$filter["oid"] = $arr["id"];
			$ol = new object_list($filter);
		}
		elseif($arr["code"] || $arr["name"])
		{
			if($arr["code"])
			{
				$filter["kood"] = $arr["code"];
			}
			else
			{
				$filter["name"] = $arr["name"];
			}
			$ol = new object_list($filter);
		}
		else
		{
			$ol = new object_list();
		}
		if(!is_object($o = reset($ol->arr())))
		{
			if(!$arr["parent"])
			{
				$arr["parent"] = $this->id();
			}
			$o = new object();
			$o->set_class_id(CL_CRM_SECTOR);
			$o->set_parent($arr["parent"]);
			$o->set_name($arr["name"]);
			$o->set_prop("kood" , $arr["code"]);
			$o->save();
		}
		$this->connect(array("to" => $o->id(),  "type" => "RELTYPE_TEGEVUSALAD"));
	}

	/** Adds category
		@attrib api=1 params=pos
		@param id optional type=oid
			category object id
	**/
	public function add_category($id)
	{
		$cat = obj($id);
		$cat->connect(array("to" => $this->id(),  "type" => "RELTYPE_CUSTOMER"));
	}

	/** Adds address
		@attrib api=1 params=name
		@param county optional type=string/oid
			county
		@param city optional type=string/oid
			city
		@param address optional type=string
			street/village etc
		@param index optional type=string
		@param parent optional type=oid
			parent for new address object
		@return oid
			address object id
	**/
	public function add_address($arr)
	{
		if(!$arr["parent"])
		{
			$arr["parent"] = $this->id();
		}
		if($arr["use_existing"])
		{
			$o = $this->get_first_obj_by_reltype("RELTYPE_ADDRESS");
		}
		if(!$o)
		{
			$o = new object();
			$o->set_class_id(CL_CRM_ADDRESS);
			$o->set_parent($arr["parent"]);
		}

		$o->set_name($arr["address"]);
		$o->set_prop("aadress" , $arr["address"]);
		$o->set_prop("postiindeks" , $arr["index"]);
		$o->save();

/*		$conns = $this->connections_from(array("type" => "RELTYPE_ADDRESS"));
		if(!sizeof($conns))
		{
			$this->set_prop("address_id" , $o->id());
			$this->save();
		}*/

		$this->connect(array("to" => $o->id(),  "type" => "RELTYPE_ADDRESS"));
		if($arr["county"])
		{
			$o->set_county($arr["county"]);
		}
		if($arr["city"])
		{
			$o->set_city($arr["city"]);
		}
		return $o->id();
	}

	/** Adds address
		@attrib api=1 params=name
		@param county optional type=string/oid
			county
		@param city optional type=string/oid
			city
		@param address optional type=string
			street/village etc
		@param index optional type=string
		@param parent optional type=oid
			parent for new address object
		@return oid
			address object id
	**/
	public function set_address($arr)
	{
		$arr["use_existing"] = 1; 
		return $this->add_address($arr);
	}

	public function add_url($address)
	{
		$mo = new object();
		$mo->set_class_id(CL_EXTLINK);
		$mo->set_parent($this->id());
		$mo->set_name($address);
		$mo->set_prop("url" , $address);
		$mo->save();

		$conns = $this->connections_from(array("type" => "RELTYPE_URL"));
		if(!sizeof($conns))
		{
			$this->set_prop("url_id" , $mo->id());
			$this->save();
		}
		$this->connect(array("to" =>$mo->id(), "type" => "RELTYPE_URL"));
		return $mo->id();
	}

	public function change_url($address)
	{
		$conns = $this->connections_from(array("type" => "RELTYPE_URL"));
		foreach($conns as $conn)
		{
			$conn->delete();
		}
		$urlid = $this->add_url($address);
		return $urlid;
	}
	function get_faxes()
	{
		$ret = array();
		$conns = $this->connections_from(array("type" => "RELTYPE_TELEFAX"));
		foreach($conns as $conn)
		{
			$ret[]= $conn->prop("to.name");
		}
		return $ret;
	}

	function get_phones()
	{
		$ret = array();
		$conns = $this->connections_from(array("type" => "RELTYPE_PHONE"));
		foreach($conns as $conn)
		{
			$ret[]= $conn->prop("to.name");
		}
		return $ret;
	}

	/** returns customer relation creator
		@attrib api=1
		@returns string
	**/	
	public function get_cust_rel_creator_name()
	{
		$o = $this->get_customer_relation();
		if(is_object($o))
		{
			return $o->prop("cust_contract_creator.name");
		}
		return "";
	}

	/** returns customer relation object
		@attrib api=1 params=pos
		@param crea_if_not_exists optional
			if no customer relation object, makes one
		@param my_co optional
		@returns object
	**/	
	public function get_customer_relation($my_co = null, $crea_if_not_exists = false)
	{
		if ($my_co === null)
		{
			$my_co = get_current_company();
		}

		if (!is_object($my_co) || !is_oid($my_co->id()))
		{
			return;
		}

		if ($this->id() == $my_co)
		{
			return false;
		}

		static $gcr_cache;
		if (!is_array($gcr_cache))
		{
			$gcr_cache = array();
		}
		if (isset($gcr_cache[$this->id()][$crea_if_not_exists][$my_co->id()]))
		{
			return $gcr_cache[$this->id()][$crea_if_not_exists][$my_co->id()];
		}

		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
			"buyer" => $this->id(),
			"seller" => $my_co
		));
		if ($ol->count())
		{
			$gcr_cache[$this->id()][$crea_if_not_exists][$my_co->id()] = $ol->begin();
			return $ol->begin();
		}
		else
		if ($crea_if_not_exists)
		{
			$my_co = obj($my_co);
			$o = obj();
			$o->set_class_id(CL_CRM_COMPANY_CUSTOMER_DATA);
			$o->set_name(t("Kliendisuhe ").$my_co->name()." => ".$this->name());
			$o->set_parent($my_co->id());
			$o->set_prop("seller", $my_co->id());
			$o->set_prop("buyer", $this->id());
			$o->save();
			$gcr_cache[$this->id()][$crea_if_not_exists][$this->id()] = $o;
			return $o;
		}
	}

	function faculties($arr = array())
	{
		$r = $this->get_sections();
		if(isset($arr["return_as_odl"]) && $arr["return_as_odl"])
		{
			$ids = $r->count() > 0 ? $r->ids() : -1;
			$arr["props"] = isset($arr["props"]) && is_array($arr["props"]) ? $arr["props"] : array(
				CL_CRM_SECTION => array("oid", "name")
			);
			$r = new object_data_list(
				array(
					"class_id" => CL_CRM_SECTION,
					"oid" => $ids,
					"site_id" => array(),
					"lang_id" => array(),
				),
				$arr["props"]
			);
		}
		return $r;
	}

	/** returns customer relation object
		@attrib api=1 params=pos
		@param form optional
			form oid or name
		@returns object
	**/	
	public function set_legal_form($form)
	{
		if(!$form)
		{
			return false;
		}
		if(is_oid($form))
		{
			$form_id = $form;
		}
		else
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_CORPFORM,
				"site_id" => array(),
				"lang_id" => array(),
				"name" => $form,
			));
	
			if($ol->count())
			{
				$form_id = reset($ol->ids());
			}
			else
			{
				$o = new object();
				$o->set_class_id(CL_CRM_CORPFORM);
				$o->set_parent($this->parent());
				$o->set_name($form);
				$o->save();
				$form_id = $o->id();
			}
		}

		$this->set_prop("ettevotlusvorm" , $form_id); 
		$this->save();
		return $form_id;
	}

	/** sets the default email adress content or creates it if needed **/
	private function set_fake_email($mail)
	{
		$n = false;
		
		if($GLOBALS["object_loader"]->cache->can("view", $this->prop("email_id")))
		{
			$eo = obj($this->prop("email_id"));
		}
		else
		{
			$eo = $this->get_first_obj_by_reltype("RELTYPE_EMAIL");
			if($eo === false)
			{
				$eo = obj();
				$eo->set_class_id(CL_ML_MEMBER);
				$eo->set_parent($this->id());
				$eo->set_prop("name", $this->name());
			}
			$n = true;
		}

		$eo->set_prop("mail", $mail);
		$eo->save();
		
		if ($n)
		{
			$this->set_prop("email_id", $eo->id());
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
		if(!is_oid($this->id()))
		{
			$this->save();
		}
		$n = false;
		if ($GLOBALS["object_loader"]->cache->can("view", $this->prop("phone_id")))
		{
			$eo = obj($this->prop("phone_id"));
		}
		else
		{
			$eo = $this->get_first_obj_by_reltype("RELTYPE_PHONE");
			if($eo === false)
			{
				$eo = obj();
				$eo->set_class_id(CL_CRM_PHONE);
				$eo->set_parent($this->id());
			}
			$n = true;
		}
		$conns = connection::find(array("from" => $this->id(), "to" => $eo->id(), "from.class_id" => CL_CRM_COMPANY, "reltype" => "RELTYPE_PHONE"));
		if(count($conns) > 0)
		{
			$eo->conn_id = reset(array_keys($conns));
		}

		$eo->set_name($phone);
		aw_disable_acl();
		$eo->save();
		aw_restore_acl();

		$this->set_prop("phone_id", $eo->id());
		$this->save();
		$this->connect(array(
			"type" => "RELTYPE_PHONE",
			"to" => $eo->id()
		));
	}

	private function set_fake_url($url)
	{
		$n = false;
		if ($GLOBALS["object_loader"]->cache->can("view", $this->prop("url_id")))
		{
			$eo = obj($this->prop("url_id"));
		}
		else
		{
			$eo = $this->get_first_obj_by_reltype("RELTYPE_URL");
			if($eo === false)
			{
				$eo = obj();
				$eo->set_class_id(CL_EXTLINK);
				$eo->set_parent($this->id());
			}
			$n = true;
		}

		$eo->set_prop("url", $url);
		$eo->save();
		
		if ($n)
		{
			$this->set_prop("url_id", $eo->id());
			$this->save();
			$this->connect(array(
				"type" => "RELTYPE_URL",
				"to" => $eo->id()
			));
		}
	}

	private function set_fake_address_prop($k, $v)
	{
		$pmap = array(
			"fake_address_country" => "riik",
			"fake_address_country_relp" => "riik",
			"fake_address_county" => "maakond",
			"fake_address_county_relp" => "maakond",
			"fake_address_city" => "linn",
			"fake_address_city_relp" => "linn",
			"fake_address_postal_code" => "postiindeks",
			"fake_address_address" => "aadress",
			"fake_address_address2" => "aadress2"
		);
		$n = false;
		if ($GLOBALS["object_loader"]->cache->can("view", $this->prop("contact")))
		{
			$eo = obj($this->prop("contact"));
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
				if(strlen(trim($v)) > 0)
				{
					$this->_adr_set_via_rel($eo, $pmap[$k], $v);
				}
				break;

			case "fake_address_county_relp":
			case "fake_address_city_relp":
			case "fake_address_country_relp":
				if(is_oid($v))
				{
					$eo->set_prop($pmap[$k], $v);
				}
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
			$this->set_prop("contact", $eo->id());
			$this->save();
		}
	}

	private function _adr_set_via_rel($o, $prop, $val)
	{
		$pl = $o->get_property_list();
		$rl = $o->get_relinfo();

		$ol = new object_list(array(
			"class_id" => $rl[$pl[$prop]["reltype"]]["clid"][0],
			"name" => $val,
			"limit" => 1,
		));
		if ($ol->count() > 0)
		{
			$ro = $ol->begin();
		}
		else
		{

			$ro = obj();
			$ro->set_class_id($rl[$pl[$prop]["reltype"]]["clid"][0]);
			$ro->set_parent(is_oid($o->id()) ? $o->id() : $o->parent());
			$ro->set_name($val);
			$ro->save();
		}

		$o->set_prop($prop, $ro->id());
	}


	/** goes through all the relations and returns a set of id
		@attrib api=1
		@returns array
	**/
	public function get_customers_for_company()
	{
		$data = array();
		$impl = array();
		$impl = $this->get_workers()->ids();
		$impl[] = $this->id();
		// also, add all orderers from projects where the company is implementor
		$ol = new object_list(array(
			"class_id" => CL_PROJECT,
			"CL_PROJECT.RELTYPE_IMPLEMENTOR" => $impl,
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($ol->arr() as $o)
		{
			foreach($o->get_customer_ids() as $ord)
			{
				if ($ord)
				{
					$data[$ord] = $ord;
				}
			}
		}

		$conns = $this->connections_from(array(
			"type" => "RELTYPE_CUSTOMER",
		));
		foreach($conns as $conn)
		{
			$data[$conn->prop('to')] = $conn->prop('to');
		}
		return $data;
	}

	/** returns all projects where company is customer
		@attrib api=1
		@returns object list
	**/
	public function get_projects_as_customer($arr = array())
	{
		$ol = new object_list(array(
			"class_id" => CL_PROJECT,
			"CL_PROJECT.RELTYPE_ORDERER" => $this->id(),
			"lang_id" => array(),
			"site_id" => array()
		));
		return $ol;
	}

}

?>
