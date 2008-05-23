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
	
	function get_lifespan()
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
			$s_out = ($i_temp = round($i_lifespan_hours))==1 ? $i_temp." ".t("tund") : $i_temp." ".t("tundi");
		}
		else
		{
			$s_out = ($i_temp = round($i_lifespan_hours/24))==1 ? $i_temp." ".t("p&auml;ev") : $i_temp." ".t("p&auml;eva");
		}
		
		return $s_out;
	}
}
