<?php
/*

@classinfo maintainer=markop

*/
class budgeting_tax_obj extends _int_object
{
	function get_terms($from)
	{
		$filter = array(
			"class_id" => CL_BUDGETING_TAX_TERM,
			"lang_id" => array(),
			"site_id" => array(),
		);
		if($from)
		{
			$filter["from_place"] = $from;
		}

		$ol = new object_list($filter);
		return $ol;
	}
	
	function calculate_amount_to_transfer($account, $sum = 0)
	{
		if(!is_object($account))
		{
			return 0;
		}

		$rel_obj = $this->get_folder_rel_object($account , $sum);

		if(!$sum)
		{
			$m = get_instance("applications/budgeting/budgeting_model");
			$sum = $m->get_account_balance($account->id());
		}

		if(is_object($rel_obj))
		{
			return $rel_obj->get_transfer_amount($sum);
		}
		else
		{
//		if (substr($tax->prop("amount"), -1) == "%")
//		{
			return $sum * ((double)$this->prop("amount") / 100.0); 
//		}
		}

		return $tax->prop("amount");
	}

	private function get_folder_rel_object($sum)
	{
		$rel = "";
		$pri = 0;
		$ol = new object_list(array(
			"class_id" => CL_BUDGETING_TAX_FOLDER_RELATION,
			"lang_id" => array(),
			"site_id" => array(),
			"tax" => $this->id()
		));
		foreach($ol->arr() as $o)
		{
			if($o->prop("pri") > $pri) // + veel tingimuse kontrolli vaja
			{
				if($sum && $o->prop("term"))
				{
					if(substr_count(">=" , $o->prop("term")))
					{
						$term_sum = explode(">=");
						if(!($sum >= trim($term_sum[1])))
						{
							continue;
						}
					}
					elseif(substr_count("<=" , $o->prop("term")))
					{
						$term_sum = explode("<=");
						if(!($sum <= trim($term_sum[1])))
						{
							continue;
						}
					}
					elseif(substr_count(">" , $o->prop("term")))
					{
						$term_sum = explode(">");
						if(!($sum > trim($term_sum[1])))
						{
							continue;
						}
					}
					elseif(substr_count("<" , $o->prop("term")))
					{
						$term_sum = explode("<");
						if(!($sum < trim($term_sum[1])))
						{
							continue;
						}
					}

				}
				
				$pri = $o->prop("pri");
				$rel = $o;
			}
		}
		return $rel;
	}

}
?>
