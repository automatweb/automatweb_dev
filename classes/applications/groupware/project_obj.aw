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
		@param done optional type=bool
		@returns
			object_list 
	**/
	public function get_tasks($arr)
	{
		$arr["clid"] = CL_TASK;
		$filter = $this->_get_tasks_filter($arr);
		$ol = new object_list($filter);
		return $ol;
	}

	/** Returns project meetings
		@attrib api=1 params=name
		@param from optional type=int
			Filter date from
		@param to optional type=int
			Filter date to
		@param done optional type=bool
		@returns
			object_list 
	**/
	public function get_meetings($arr)
	{
		$arr["clid"] = CL_CRM_MEETING;
		$filter = $this->_get_tasks_filter($arr);
		$ol = new object_list($filter);
		return $ol;
	}

	/** Returns project calls
		@attrib api=1 params=name
		@param from optional type=int
			Filter date from
		@param to optional type=int
			Filter date to
		@param done optional type=bool
		@returns
			object_list 
	**/
	public function get_calls($arr)
	{
		$arr["clid"] = CL_CRM_CALL;
		$filter = $this->_get_tasks_filter($arr);
		$ol = new object_list($filter);
		return $ol;
	}

	/** returns all bugs related to current project
		@attrib api=1 params=name
		@param start optional
			time between start
		@param end optional
			time between end
		@param status optional type=bool
		@param participant optional type=string/oid
		@returns object list
	**/
	public function get_bugs($arr = array())
	{
		$filter = $this->_get_bugs_filter($arr);
		$ol = new object_list($filter);
		return $ol;
	}

	/** returns bugs related to current project
		@attrib api=1 params=name
		@param from optional type=int
			time between start
		@param to optional
			time between end type=int
		@param status optional type=int
			bug status
		@returns object list
	**/
	public function get_bugs_data($arr = array())
	{
		$filter = $this->_get_bugs_filter($arr);
		$bugres = array(
			CL_BUG => array("bug_status"),
		);
		$rows_arr = new object_data_list($filter , $bugres);

		return $rows_arr->list_data;
	}

	private function _get_bugs_filter($arr = array())
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_BUG,
			"project" => $this->id(),
			"sort_by" => "objects.created desc",
		);

		extract($arr);

		if ($from && $to)
		{
			$filter["CL_BUG.RELTYPE_COMMENT.created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $from-1, $to);
		}
		else
		if ($from)
		{
			$filter["CL_BUG.RELTYPE_COMMENT.created"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $from);
		}
		else
		if ($to)
		{
			$filter["CL_BUG.RELTYPE_COMMENT.created"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $to);
		}
		
		if($participant)
		{
			if(is_oid($participant))
			{
				$filter["CL_BUG.RELTYPE_MONITOR"] = $participant;
			}
			else
			{
				$filter["CL_BUG.RELTYPE_MONITOR.name"] = "%".$participant."%";;
			}

		}

		if($status)
		{
			$filter["bug_status"] = $status;
		}

		return $filter;
		
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

	private function all_rows_filter()
	{
		return array(
			"class_id" => CL_TASK_ROW,
			"bill_id" => new obj_predicate_compare(OBJ_COMP_EQUAL, ''),
			"done" => 1,
			"CL_TASK_ROW.RELTYPE_PROJECT" => $this->id(),
		);
	}

	public function get_rows_data()
	{
		$rows_filter = $this->all_rows_filter();
		$rowsres = array(
			CL_TASK_ROW => array(
				"task",
				"task.class_id",
				"time_real",
				"time_to_cust",
				"time_guess",
				"impl",
				"date",
			),
		);
		$rows_arr = new object_data_list($rows_filter , $rowsres);

		return $rows_arr->list_data;
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

	/** returns all tasks, meetings and calls data related to current project
		@attrib api=1 params=name
		@returns object list
	**/
	public function get_all_tasks_data($arr = array())
	{
		$filter = $this->_get_tasks_filter($arr);
		$bugres = array(
			CL_TASK => array("end", "is_done"),
		);
		$rows_arr = new object_data_list($filter , $bugres);

		return $rows_arr->list_data;
	}

	private function _get_tasks_filter($arr = array())
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			//"class_id" => CL_TASK,
			"brother_of" => new obj_predicate_prop("id"),
		);

		$clids = array(CL_TASK => "CL_TASK", CL_CRM_MEETING => "CL_CRM_MEETING" , CL_CRM_CALL => "CL_CRM_CALL");
		if($arr["clid"])
		{
			$search_clids = array($arr["clid"]);
		}
		else
		{
			$search_clids = array_keys($clids);
		}

		$filter["class_id"] = $search_clids;

		$project_cond = array();
		foreach($search_clids as $c)
		{
			$project_cond[$clids[$c].".RELTYPE_PROJECT"] = $this->id();
		}
		$filter[] = new object_list_filter(array(
			"logic" => "OR",
			"conditions" => $project_cond,
		));

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

		if(isset($arr["done"]))
		{
			if($arr["done"])
			{
				$filter["is_done"] = 1;
			}
			else
			{
				$filter["is_done"] = new obj_predicate_not(1);
			}
		}

		if($time_filt)
		{
			$time_cond = array();
			foreach($search_clids as $c)
			{
//				$time_cond[$clids[$c].".RELTYPE_ROW.date"] = $time_filt;
				$time_cond[$clids[$c].".start1"] = $time_filt;
				$time_cond[$clids[$c].".end"] = $time_filt;
			}
			$filter[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => $time_cond,
			));
		}

		//kui k6igile osalus, siis toimisk
		if($arr["participant"])
		{
			$rows_filter = array("class_id" => CL_TASK_ROW);
			$rows_filter["CL_TASK_ROW.RELTYPE_PROJECT"] = $this->id();
			$rows_filter["task.class_id"] = $search_clids;
			if(is_oid($arr["participant"]))
			{
				$rows_filter["CL_TASK_ROW.RELTYPE_IMPL"] = $arr["participant"];
			}
			else
			{
				$rows_filter["CL_TASK_ROW.RELTYPE_IMPL.name"] = "%".$arr["participant"]."%";
			}
			$task_ids = array();

			$rowsres = array(
				CL_TASK_ROW => array(
					"task" => "task",
				),
			);
			$rows_arr = new object_data_list($rows_filter , $rowsres);

			foreach($rows_arr->list_data as $bcs)
			{
				$task_ids[$bcs["task"]] = $bcs["task"];
			}
			if(sizeof($task_ids))
			{
				$filter["oid"] = $task_ids;
			}
			else
			{
				$filter["oid"] = 1;
			}
		}
		return $filter;
	}

}
?>