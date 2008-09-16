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

	function get_job_offers()
	{
		$r = new object_list;
		foreach($this->connections_to(array("from.class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER, "type" => "RELTYPE_ORG")) as $conn)
		{
			$r->add($conn->prop("from"));
		}
		return $r;
	}

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

	/** Adds new eployee
		@attrib api=1 params=name
		@param name optional type=string
			person name
		@return oid
			person object id
	**/
	function add_employees($data = array())
	{
		$o = new object();
		$o->set_name($data["name"] ? $data["name"] : t("(Nimetu)"));
		$o->set_class_id(CL_CRM_PERSON);
		$o->set_parent($this->id());
		$o->save();

		$this->connect(array(
			"type" => "RELTYPE_WORKERS",
			"to" => $o->id()
			)
		);
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

	function get_sub_sections($parents)
	{
		$r = array();
		foreach($parents as $parent_id)
		{
			if(!is_oid($parent_id))
			{
				$parent = obj($parent_id);
				foreach($this->connections_from(array("type" => "RELTYPE_SECTION")) as $conn)
				{
					$r->add($conn->prop("to"));
				}
			}
			$r->add($conn->prop("to"));
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
}

?>
