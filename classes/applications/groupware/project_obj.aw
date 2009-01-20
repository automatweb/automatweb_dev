<?php
/*
@classinfo maintainer=markop
*/
class project_obj extends _int_object
{
	function set_prop($pn, $pv)
	{
		switch($pn)
		{
			case "participants":
				$set = (array)$this->prop("implementor");
				foreach((array)$this->prop("orderer") as $val)
				{
					$set[$val] = $val;
				}

				foreach($set as $id)
				{
					$this->connect(array(
						"to" => $id,
						"type" => "RELTYPE_PARTICPANT"
					));
					$pv[$id] = $id;
				}
				break;
		}

		return parent::set_prop($pn, $pv);
	}

	function save()
	{
		$new = !is_oid($this->id());
		$rv = parent::save();
		if ($new && !count($this->connections_from(array("type" => "RELTYPE_IMPLEMENTOR"))))
		{
			$c = get_current_company();
			$this->connect(array(
				"to" => $c->id(),
				"type" => "RELTYPE_IMPLEMENTOR"
			));
		}
		return $rv;
	}

	/** Returns project tasks
		@attrib api=1 params=name
		@param from optional type=int
			Filter date from
		@param to optional type=int
			Filter date to
		@returns
			object_list 
	**/
	function get_tasks($arr)
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_TASK,
			"project" => $this->id(),
			"brother_of" => new obj_predicate_prop("id"),
		);

		if ($arr["from"] > 1 && $arr["to"])
		{
			$time_filt = new obj_predicate_compare(OBJ_COMP_BETWEEN, $arr["from"], $arr["to"]);
		}
		else
		if ($arr["from"] > 1)
		{
			$time_filt = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $arr["from"]);
		}
		else
		if ($arr["to"] > 1)
		{
			$time_filt = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $arr["to"]);
		}

		if($time_filt)
		{
			$filter["CL_TASK.RELTYPE_ROW.date"] = $time_filt;
		}

		$ol = new object_list($filter);
		return $ol;
	}

	/** returns all bugs related to current project
		@attrib api=1 params=pos
		@param start optional
			time between start
		@param end optional
			time between end
		@returns object list
	**/
	function get_bugs($start = null, $end=null)
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_BUG,
			"project" => $this->id(),
			"sort_by" => "objects.created desc",
		);

		if ($start && $end)
		{
			$filter["CL_BUG.RELTYPE_COMMENT.created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_OR_EQ, $start, $end);
		}
		else
		if ($start)
		{
			$filter["CL_BUG.RELTYPE_COMMENT.created"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $start);
		}
		else
		if ($end)
		{
			$filt["CL_BUG.RELTYPE_COMMENT.created"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $end);
		}

		$ol = new object_list($filter);

		return $ol;
	}

	/** returns all billable bugs related to current project
		@attrib api=1 params=pos
		@returns object list
	**/
	function get_billable_bugs()
	{
		enter_function("project::_get_billable_bugs");
/*		$all_bugs = new object_list(array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_BUG,
			"project" => $this->id(),
			"sort_by" => "objects.created desc",
			"send_bill" => 1,
		));*/
		$bugs = array();
		$bugs_list = new object_list();
		$rows_filter = $this->get_billable_bug_rows_filter();
		$rowsres = array(
			CL_TASK_ROW => array(
				"task" => "task",
			),
		);
		$rows_arr = new object_data_list($rows_filter , $rowsres);
		foreach($rows_arr->list_data as $bcs)
		{
			$bugs[$bcs["task"]] = $bcs["task"];
		}
		$bugs_list->add($bugs);
		exit_function("project::_get_billable_bugs");
		
		return $bugs_list;
	}

	private function get_billable_bug_rows_filter()
	{
		$filter = array(
			"class_id" => CL_TASK_ROW,
			"bill_id" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
			"on_bill" => 1,
			"done" => 1,
			"task.class_id" => CL_BUG,
			"CL_TASK_ROW.task(CL_BUG).send_bill" => 1,
			"CL_TASK_ROW.RELTYPE_PROJECT" => $this->id(),
		);
		return $filter;
	}

	/** Returns an object_list with all bug comments for the project
		@attrib api=1 params=pos

		@param date_from optional type=int
			Filter date from

		@param date_to optional type=int
			Filter date to
	
		@returns
			object_list instance with the bug comments in it
	**/
	function get_bug_comments($date_from = null, $date_to = null)
	{
		$bug_ol = $this->get_bugs();
		if(!sizeof($bug_ol->ids()))
		{
			return new object_list();
		}
		$filt = array(
			"class_id" => array(CL_BUG_COMMENT,CL_TASK_ROW),
			"parent" => $bug_ol->ids(),
			"lang_id" => array(),
			"site_id" => array(),
		);
		if ($date_from !== null && $date_to !== null)
		{
			$filt["created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, ($date_from - 1), ($date_to + 1));
		}
		else
		if ($date_from !== null)
		{
			$filt["created"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $date_from);
		}
		else
		if ($date_to !== null)
		{
			$filt["created"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $date_to);
		}
		$com_ol = new object_list($filt);
		return $com_ol;
	}

	function get_calls()
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_CRM_CALL,
			"project" => $this->id(),
		);
		$ol = new object_list($filter);
		return $ol;
	}

	function get_meetings()
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_CRM_MEETING,
			"project" => $this->id(),
		);
		$ol = new object_list($filter);
		return $ol;
	}
	
	function get_products()
	{
		$ol = new object_list($this->connections_from(array(
				"type" => "RELTYPE_PRODUCT"
		)));
		// = new object_list($filter);
		return $ol;
	}

	/** Returns number of hours spent on the project, group by person and object type (taskt,bug,call,..)
		@attrib api=1 params=pos

		@param person_filter optional type=array 
			array { person_id, ... } if array then only the given persons stats are returned if not, then all persons in the project

		@param date_filter optional type=array
			array { from => timestamp, to => timestamp } if set, then data is filtered by the gievn dates

		@returns
			array { 
				person_id => array { 
					class_id => array { 
						paid => hour_count, 
						unpaid => hour_count,
						act_type => array {
							cl_crm_activity_stats_type oid => hours_count,
							...
						}
					}, 
					... 
				}, 
				... 
			}
	**/
	function stats_get_by_person($person_filter = null, $date_filter = null)
	{
		$rv = array();

		$this->_stats_insert_crm($rv, $person_filter, $date_filter);
		$this->_stats_insert_bugs($rv, $person_filter, $date_filter);
		
		return $rv;
	}

	private function _stats_insert_crm(&$rv, $person_filter, $date_filter)
	{
		// classes that take time from projects crm_call crm_meeting task bug
		$filt = array(
			"class_id" => array(CL_CRM_CALL, CL_CRM_MEETING, CL_TASK),
			"lang_id" => array(),
			"site_id" => array(),
			"project" => $this->id()
		);
		$ol = new object_list($filt);

		$member2o = $this->_stats_get_member_list($ol->ids());
		foreach($ol->arr() as $o)
		{
			switch($o->class_id())
			{
				case CL_CRM_CALL:
				case CL_CRM_MEETING:
					foreach(safe_array($member2o[$o->id()]) as $person)
					{
						$rv[$person][$o->class_id()] = array(
							"paid" => (double)$o->time_to_cust,
							"unpaid" => (double)$o->time_real
						);
					}
					break;

				case CL_TASK:
					foreach(safe_array($member2o[$o->id()]) as $person)
					{
						$rv[$person][$o->class_id()] = array(
							"paid" => (double)$o->num_hrs_to_cust,
							"unpaid" => (double)$o->num_hrs_real
						);
						foreach($o->get_all_rows() as $row_o)
						{
							$rv[$person][$o->class_id()]["paid"] += (double)$o->time_to_cust; 
							$rv[$person][$o->class_id()]["unpaid"] += (double)$o->time_real; 
						}
					}
					break;
			}
		}
	}

	/** returns task => array { person,...} **/
	private function _stats_get_member_list($ids)
	{
		if (!count($ids))
		{
			return array();
		}
		$c = new connection();
		$rels = $c->find(array(
			"to" => $ids,
			"from.class_id" => CL_CRM_PERSON
		));
		$rv = array();
		foreach($rels as $rel)
		{
			$rv[$rel["to"]][] = $rel["from"];
		}
		return $rv;
	}

	private function _stats_insert_bugs(&$rv, $person_filter, $date_filter)
	{
		$filt_bug = array(
			"class_id" => CL_BUG,
			"lang_id" => array(),
			"site_id" => array(),
			"project" => $this->id()
		);
		$ol2 = new object_list($filt_bug);
		$bug_ids = $ol2->ids();

		$bug_comments = new object_list(array(
			"class_id" => array(CL_BUG_COMMENT,CL_TASK_ROW),
			"parent" => $bug_ids,
			"lang_id" => array(),
			"site_id" => array(),
		));
		foreach($bug_comments->arr() as $com)
		{
			$person_id = $this->_get_person_from_user($com->createdby());

			$rv[$person_id][CL_BUG]["paid"] += (double)$com->add_wh_cust;
			$rv[$person_id][CL_BUG]["unpaid"] += (double)$com->add_wh;
			$rv[$person_id][CL_BUG]["act_type"][(int)$com->activity_stats_type] += (double)$com->add_wh;
		}
	}

	private function _get_person_from_user($uid)
	{
		static $cache;
		if (!isset($cache[$uid]))
		{
			$cache[$uid] = get_instance(CL_USER)->get_person_for_uid($uid)->id();
		}

		return $cache[$uid];
	}

	/** returns time spent with bugs
		@attrib api=1 params=pos
		@returns double
			spent time in hours
	**/
	function get_bugs_time($start = null, $end = null)
	{
		$sum = 0;
		if(!$start && !$end)
		{
			$ol = $this->get_bugs();
			foreach($ol->arr() as $o)
			{
				$sum += $o->prop("num_hrs_real");
			}
		}
		else
		{
			$comments = $this->get_bug_comments($start , $end);
			foreach($comments->arr() as $com)
			{
				$sum+= (double)$com->prop("add_wh");
			}
		}
		return $sum;
	}


	function get_project_bugs()
	{return $this->get_bugs();
	}

	/** Returns orderer id
		@attrib api=1
		@returns oid
			Orderer object id
	**/
	public function get_orderer()
	{
		$orderers = $this->get_customer_ids();
		$orderer = reset($this->get_customer_ids());
		if($orderer && $this->prop("orderer") != $orderer)
		{
			$this->set_prop("orderer" , $orderer);
			$this->save();
		}
		return $orderer;
	}

	/** Returns implementor id
		@attrib api=1
		@returns oid
			Implementor object id
	**/
	public function get_implementor()
	{
		$impl = $this->prop("implementor");
		if (is_array($impl))
		{
			$impl = reset($impl);
		}
		return $impl;
	}

	public function get_customer_ids()
	{
		$ret = array();
		foreach($this->connections_from(array("type" => "RELTYPE_ORDERER")) as $c)
		{
			$ret[$c->prop("to")] = $c->prop("to");
		}
		return $ret;
	}

	private function get_bills_filter($status = null)
	{
		$ids = array();
		$task_rows = new object_list(array(
			"class_id" => CL_TASK_ROW,
			"lang_id" => array(),
			"site_id" => array(),
			"project" => $this->id(),
		));

		foreach($task_rows->arr() as $tr)
		{
			$ids[$tr->prop("bill")] = $tr->prop("bill");
		}

		if(!sizeof($ids))
		{
			$ids[] = 1;
		}

		$filter = array(
			"class_id" => CL_CRM_BILL,
			"lang_id" => array(),
			"site_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"oid" => $ids,
					"CL_CRM_BILL.RELTYPE_PROJECT" => $this->id(),
				)
			)),
		);

		if(isset($status))
		{
			$filter["state"] = $status;
		}
		return $filter;

	}

	/** Returns project bills
		@attrib api=1
		@param status optional type=int
			Filter date to
		@returns object list
	**/
	public function get_bills($arr = array())
	{
		$ol = new object_list(
			$this->get_bills_filter($arr["status"])
		);
		return $ol;
	}

	function get_billable_deal_tasks()
	{
//---------------------------------------------uuele systeemile ka vaja... osalustega projektidele
		$ol = new object_list(array(
			"class_id" => array(CL_TASK, CL_CRM_MEETING,CL_CRM_CALL),
			"send_bill" => 1,
			"lang_id" => array(),
			"brother_of" => new obj_predicate_prop("id"),
			"deal_price" => new obj_predicate_compare(OBJ_COMP_GREATER, 0),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_TASK.RELTYPE_PROJECT" => $this->id(),
					"CL_CRM_MEETING.RELTYPE_PROJECT" => $this->id(),
					"CL_CRM_CALL.RELTYPE_PROJECT" => $this->id(),

				)
			)),
		));
		return $ol;
	}

	function get_billable_expenses()
	{
/*		$ol = new object_list(array(
			"class_id" => CL_CRM_EXPENSE,
			"send_bill" => 1,
			"lang_id" => array(),
			"brother_of" => new obj_predicate_prop("id"),
			"deal_price" => new obj_predicate_compare(OBJ_COMP_GREATER, 0),
		));*/

		//---------------------------suht vigane on kulude m2rkimine arvele... see yle vaadata
		$ol = new object_list();
		return $ol;
	}

	function get_billable_rows()
	{
		$ol = new object_list(array(
			"class_id" => CL_TASK_ROW,
			"bill_id" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
			"on_bill" => 1,
			"done" => 1,
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_TASK_ROW.task(CL_TASK).send_bill" => 1,
					"CL_TASK_ROW.task(CL_BUG).send_bill" => 1,
					"CL_TASK_ROW.task(CL_CRM_MEETING).send_bill" => 1,
					"CL_TASK_ROW.task(CL_CRM_CALL).send_bill" => 1,
				)
			)),
			"CL_TASK_ROW.RELTYPE_PROJECT" => $this->id(),
		));
		return $ol;
	}

	function get_billable_task_rows()
	{
		$ol = new object_list(array(
			"class_id" => CL_TASK_ROW,
			"bill_id" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
			"on_bill" => 1,
			"done" => 1,
			"task.class_id" => array(CL_TASK, CL_CRM_MEETING,CL_CRM_CALL),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_TASK_ROW.task(CL_TASK).send_bill" => 1,
					"CL_TASK_ROW.task(CL_CRM_MEETING).send_bill" => 1,
					"CL_TASK_ROW.task(CL_CRM_CALL).send_bill" => 1,
				)
			)),
			"CL_TASK_ROW.RELTYPE_PROJECT" => $this->id(),
		));
		return $ol;
	}

	/** Adds bill
		@attrib api=1
	**/
	public function add_bill()
	{
		$bill = obj();
		$bill->set_class_id(CL_CRM_BILL);
		$bill->set_parent($this->id());
		$bill->save();

		$ser = get_instance(CL_CRM_NUMBER_SERIES);
		$bno = $ser->find_series_and_get_next(CL_CRM_BILL,0,time());
		if (!$bno)
		{
			$bno = $bill->id();
		}

		$bill->set_prop("bill_no", $bno);
		$bill->set_prop("bill_trans_date", time());
		$bill->set_name(sprintf(t("Arve nr %s"), $bill->prop("bill_no")));
		
		$bill->set_project($this->id());
		$cust = $this->get_orderer();
		if ($cust)
		{
			$bill->set_prop("customer", $cust);
		}

// 		$impl = $this->get_implementor();
// 		if ($impl)
// 		{
// 			$bill->set_prop("impl", $impl);
// 		}
		$bill->set_impl();
		$bill->set_prop("bill_date", time());
		$bill->set_due_date();

		$bill->save();
		return $bill;
	}

	public function get_goals($parent = null)
	{
/*		$goals = new object_list(array(
			"class_id" => array(CL_TASK,CL_CRM_CALL,CL_CRM_MEETING),
			"project" => $this->id(),
			"predicates" => $parent,
			"brother_of" => new obj_predicate_prop("id")
		));
*/
		$goals = new object_list(array(
			"class_id" => array(CL_TASK,CL_CRM_CALL,CL_CRM_MEETING,CL_BUG),
//			"project" => $arr["obj_inst"]->id(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"oid" => $ids,
					"CL_TASK.RELTYPE_PROJECT" => $this->id(),
					"CL_CRM_MEETING.RELTYPE_PROJECT" => $this->id(),
					"CL_CRM_CALL.RELTYPE_PROJECT" => $this->id(),
					"CL_BUG.RELTYPE_PROJECT" => $this->id(),
				)
			)),
//			"predicates" => $parent,
			"brother_of" => new obj_predicate_prop("id")
		));
 
		//kuna nyyd asi peaks toimuma nii et mis omab connectionit, on
//		foreach($this->connections_to(array("type" => 4)) as $c)
//		{
//			$goals->add($c->prop("from"));
//		}
		return $goals;
	}
}
?>