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
		
	function get_tasks()
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_TASK,
			"project" => $this->id(),
		);
		$ol = new object_list($filter);
		return $ol;
	}

	function get_bugs()
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_BUG,
			"project" => $this->id(),
		);
		$ol = new object_list($filter);
		return $ol;
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
		$filt = array(
			"class_id" => CL_BUG_COMMENT,
			"parent" => $bug_ol->ids(),
			"lang_id" => array(),
			"site_id" => array(),
		);
		if ($date_from !== null && $date_to !== null)
		{
			$filt["created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_OR_EQ, $date_from, $date_to);
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
			"class_id" => CL_BUG_COMMENT,
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
		@attrib api=1
		@returns double
			spent time in hours
	**/
	function get_bugs_time()
	{
		$sum = 0;
		$ol = $this->get_project_bugs();
		foreach($ol->arr() as $o)
		{
			$sum += $o->prop("num_hrs_real");
		}
		return $sum;
	}

	/** returns all bugs related to current project
		@attrib api=1
		@returns object list
	**/
	function get_project_bugs()
	{
		$ol = new object_list(array(
			"class_id" => CL_BUG,
			"project" => $this->id(),
			"lang_id" => array(),
			"site_id" => array(),
			"sort_by" => "objects.created desc",
		));
		return $ol;
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

}
?>