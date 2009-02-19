<?php

class mrp_pricelist_obj extends _int_object
{
	/** Returns array of resources for this price list
		@attrib api=1
	**/
	function get_resource_list()
	{	
		$conns = $this->connections_to(array("from.class_id" => CL_MRP_ORDER_CENTER));
		$c = reset($conns);
		if (!$c)
		{
			return array();
		}

		$ot = new object_tree(array(
			"parent" => $c->from()->mrp_workspace()->resources_folder,
			"class_id" => array(CL_MRP_RESOURCE,CL_MENU),
			"lang_id" => array(),
			"site_id" => array()
		));
		$rv = array();

		foreach($ot->to_list()->arr() as $item)
		{
			if ($item->class_id() == CL_MRP_RESOURCE)
			{
				$rv[] = $item;
			}
		}
		return $rv;
	}

	function get_ranges_for_resource($res)
	{
		$ol = new object_list(array(
			"class_id" => CL_MRP_PRICELIST_ROW,
			"lang_id" => array(),
			"site_id" => array(),
			"pricelist" => $this->id(),
			"resource" => $res->id()
		));
		return $ol->arr();
	}

	function set_ranges_for_resource($res, $d)
	{
		foreach(safe_array($d) as $idx => $row)
		{
			if ($idx == -1)
			{
				$r = obj();
				$r->set_parent($this->id());
				$r->set_class_id(CL_MRP_PRICELIST_ROW);
				$r->set_name(sprintf(t("Hinnakirja %s rida ressursile %s"), $this->name(), $res->name()));
				$r->pricelist = $this->id();
				$r->set_prop("resource", $res->id());
			}
			else
			if ($this->can("view", $idx))
			{
				$r = obj($idx);
			}
			else
			{
				continue;
			}

			if ($row["cnt_from"] < 1 && $row["cnt_to"] < 1)
			{
				if (is_oid($r->id()))
				{
					$r->delete();
				}
				continue;
			}

			$r->item_price = $row["item_price"];
			$r->config_price = $row["config_price"];
			$r->cnt_from = $row["cnt_from"];
			$r->cnt_to = $row["cnt_to"];
			$r->save();
		}
	}
}

?>
