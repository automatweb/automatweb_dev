<?php

class crm_company_obj extends _int_object
{
	function set_prop($name,$value)
	{
		if($name == "name")
		{
			$value = htmlspecialchars($value);
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

	function get_mails()
	{
		$ret = array();
		$conns = $this->connections_from(array("type" => "RELTYPE_EMAIL"));
		foreach($conns as $conn)
		{
			$ret[]= $conn->prop("to.name");
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

		$this->connect(array("to" =>$mo->id(),  "type" => "RELTYPE_EMAIL"));
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
		$o = new object();
		$o->set_class_id(CL_CRM_ADDRESS);
		$o->set_parent($arr["parent"]);
		$o->set_name($arr["address"]);
		$o->set_prop("aadress" , $arr["address"]);
		$o->save();
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
}

?>
