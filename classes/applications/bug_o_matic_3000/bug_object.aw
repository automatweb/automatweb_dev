<?php
/*
@classinfo  maintainer=robert
*/

class bug_object extends _int_object
{
	function save()
	{
		// before saving, set default props if they are not set yet
		if (!is_oid($this->id()))
		{
			$this->_set_default_bug_props();
		}
		return parent::save();
	}

	function _set_default_bug_props()
	{
		if (!$this->prop("orderer"))
		{
			$c = get_current_company();
			if($c)
			{
				$this->set_prop("orderer", $c->id());
			}
		}
		if (!$this->prop("orderer_unt"))
		{
			$p = get_current_person();
			if($p)
			{
				$sets = $p->prop("org_section");
			}
			if (is_array($sets))
			{
				$sets = reset($sets);
			}
			$this->set_prop("orderer_unit", $sets);
		}
		if (!$this->prop("orderer_person"))
		{
			$p = get_current_person();
			if($p)
			{
				$this->set_prop("orderer_person", $p->id());
			}
		}
	}

	function sum_guess()
	{
		$sum = 0;
		if($this->prop("num_hrs_guess"))
		{
			$sum = $this->prop("num_hrs_guess") * $this->prop("skill_used.hour_price");
		}
		return $sum;
	}
	
	function get_lifespan($arr)
	{
		// calculate timestamp
		$i_created = $this->created();
		if ($this->prop("bug_status") == BUG_CLOSED)
		{
			$o_bug_comments = new object_list(array(
				"class_id" => CL_BUG_COMMENT,
				"lang_id" => array(),
				"site_id" => array(),
				"parent" => $this->id(),
				"sort_by" => "objects.created"
			));
			
			$i_lifespan = end($o_bug_comments->arr())->created() - $i_created;
		}
		else
		{
			$i_lifespan = time() - $i_created;
		}
		
		// format output
		$i_lifespan_hours = $i_lifespan/3600;
		if ($i_lifespan_hours<=24)
		{
			if ($arr["only_days"])
			{
				if ($arr["without_string_prefix"])
				{
					$s_out = round($i_lifespan_hours/24);
				}
				else
				{
					$s_out = ($i_temp = round($i_lifespan_hours/24))==1 ? $i_temp." ".t("tund") : $i_temp." ".t("tundi");
				}
			}
			else
			{
				if ($arr["without_string_prefix"])
				{
					$s_out = round($i_lifespan_hours);
				}
				else
				{
					$s_out = ($i_temp = round($i_lifespan_hours))==1 ? $i_temp." ".t("tund") : $i_temp." ".t("tundi");
				}
			}
		}
		else
		{
			if ($arr["without_string_prefix"])
			{
				$s_out = round($i_lifespan_hours/24);
			}
			else
			{
				$s_out = ($i_temp = round($i_lifespan_hours/24))==1 ? $i_temp." ".t("p&auml;ev") : $i_temp." ".t("p&auml;eva");
			}
		}
		
		return $s_out;
	}

	/** returns last comment
		@attrib api=1
		@returns object
	**/
	public function get_last_comment()
	{
		$comments = $this->connections_from(array(
			"type" => "RELTYPE_COMMENT",
		));
		if(!sizeof($comments))
		{
			return "";
		}
		$comments = array_values($comments);
		$connection = $comments[sizeof($comments) - 1];
		$obj = $connection->to();
		return $obj;
	}

	/** returns last bug comment time
		@attrib api=1
		@returns timestamp
			bug comment time, if no comments, then bug created time
	**/
	public function get_last_comment_time()
	{
		$comment = $this->get_last_comment();
		if(!$comment)
		{
			return $this->created();
		}
		return $comment->created();
	}

	/** returns all bug comments
		@attrib api=1
		@returns object list
	**/
	public function get_bug_comments()
	{
		$ol = new object_list();
		$comments = $this->connections_from(array(
			"type" => "RELTYPE_COMMENT",
		));
		
		$ol->add($comments);
		return $ol;
	}

	/** 
		@attrib api=1 params=pos
		@param start
		@param end
		@returns double
	**/
	public function get_bug_comments_time($start = null, $end = null)
	{
		$sum = 0;
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_BUG_COMMENT,
			"parent" => $this->id(),
			"sort_by" => "objects.created desc",
		);

		if ($start && $end)
		{
			$filter["CL_BUG_COMMENT.created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, ($start - 1), ($end+ 1));
		}
		else
		if ($start)
		{
			$filter["CL_BUG_COMMENT.created"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $start);
		}
		else
		if ($end)
		{
			$filt["CL_BUG_COMMENT.created"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $end);
		}

		$ol = new object_list($filter);

		foreach($ol->arr() as $o)
		{
			$sum+= (double)$o->prop("add_wh");
		}

		return $sum;
	}

	//see testimiseks praegu annab k6ik kommentaarid.. pole veel arvega yhendamist tehtud
	/** returns bug comments without bill 
		@attrib api=1
		@returns object list
	**/
	public function get_billable_comments($arr)
	{
		$ol = new object_list();
		$comments = $this->connections_from(array(
			"type" => "RELTYPE_COMMENT",
		));
		$inst = get_instance(CL_BUG);
		foreach($comments as $c)
		{
			$comment = $c->to();
//selle asemele peaks miski ilus filter olema hoopis
			if(
				($arr["start"] && $arr["start"] > $comment->created()) || 
				($arr["end"] && $arr["end"] < $comment->created())
			)
			{
				continue;
			}
			if(!$inst->can("view" , $comment->prop("bill")))
			{
				$ol->add($comment->id());
			}
		}
		return $ol;
	}

	/** returns bug orderer
		@attrib api=1
		@returns oid
	**/
	public function get_orderer()
	{
		return $this->prop("orderer");
	}
}
