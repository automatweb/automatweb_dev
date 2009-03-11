<?php

classload("mrp/mrp_header");

class mrp_job_obj extends _int_object
{
	function prop($k)
	{
		if($k === "real_length" || $k === "length_deviation")
		{
			if(!is_numeric(parent::prop($k)) && $this->prop("state") == MRP_STATUS_DONE && is_oid($this->id()))
			{
				if($k === "length_deviation")
				{
					$this->set_prop($k, $this->get_deviation());
				}
				else
				{
					$this->set_prop($k, $this->get_real($k));
				}
				$this->save();
			}
			else
			{
				return (int)parent::prop($k);
			}
		}

		return parent::prop($k);
	}

	function get_deviation()
	{
		return $this->prop("real_length") - $this->prop("planned_length");
	}

	function get_real($k)
	{
		$case = $this->prop("project");
		$res = $this->prop("resource");
		$job_id = $this->id();

		$v = $this->instance()->db_fetch_field("
			SELECT SUM(length) as length_sum FROM mrp_stats WHERE
				case_oid = $case AND
				resource_oid = $res AND
				job_oid = $job_id",
			"length_sum");

		return $v ? (int)$v : 0;
	}

	function get_resource()
	{
		return $this->get_first_obj_by_reltype("RELTYPE_MRP_RESOURCE");
	}

	function save_materials($arr)
	{
		$res = $this->get_first_obj_by_reltype("RELTYPE_MRP_RESOURCE");
		if($res)
		{
			$conn = $res->connections_to(array(
				"from.class_id" => CL_MATERIAL_EXPENSE_CONDITION,
			));
			$conn2 = $this->connections_to(array(
				"from.class_id" => CL_MATERIAL_EXPENSE,
			));
			foreach($conn2 as $c)
			{
				$o = $c->from();
				$prod = $o->prop("product");
				$prods[$prod] = $o->id();
			}

			foreach($conn as $c)
			{
				$prod = $c->from()->prop("product");
				if(!$prods[$prod] && $arr["request"]["amount"][$prod])
				{
					$o = obj();
					$o->set_class_id(CL_MATERIAL_EXPENSE);
					$o->set_parent($this->id());
					$o->set_name(sprintf(t("%s kulu %s jaoks"), obj($prod)->name(), $this->name()));
					$o->set_prop("product", $prod);
					if($arr["request"]["unit"][$prod])
					{
						$o->set_prop("unit", $arr["request"]["unit"][$prod]);
					}
					$o->set_prop("amount", $arr["request"]["amount"][$prod]);
					$o->set_prop("job", $this->id());
					$o->save();
				}
				else
				{
					if($prods[$prod] && !$arr["request"]["amount"][$prod])
					{
						$eo = obj($prods[$prod]);
						$eo->delete();
					}
					elseif($prods[$prod])
					{
						$eo = obj($prods[$prod]);
						$eo->set_prop("unit", $arr["request"]["unit"][$prod]);
						$eo->set_prop("amount", $arr["request"]["amount"][$prod]);
						$eo->save();
					}
				}
			}
			$this->save();
			$conn = $this->connections_to(array(
				"from.class_id" => CL_MATERIAL_MOVEMENT_RELATION,
			));
			foreach($arr["request"]["unit"] as $prod => $unit)
			{
				if(!$arr["request"]["amount"][$prod])
				{
					continue;
				}
				$data[$prod] = array(
					"unit" => $unit,
					"amount" => $arr["request"]["amount"][$prod],
				);
			}
			if(!count($conn))
			{
				$o = obj();
				$o->set_class_id(CL_MATERIAL_MOVEMENT_RELATION);
				$o->set_parent($this->id());
				$o->set_name(sprintf(t("Materjali liikumisseos t&ouml;&ouml;ga %s"), $this->name()));
				$o->set_prop("job", $this->id());
				$o->save();
				$o->create_dn($o, $data);
			}
			else
			{
				foreach($conn as $c)
				{
					$c->from()->update_dn_rows($c->from(), $data);
				}
			}
		}
	}

	/** Adds, changes or removes source materials planned to be used by this job
	@attrib api=1 params=pos
	@param product required type=CL_SHOP_PRODUCT
	@param quantity required type=int,float
		Quantity how much or how many of $product is assessed to be required for completing this job.
	@param unit optional type=CL_UNIT
		If no unit given, product's default unit used.
	@returns void
	@comment
		If quantity is 0, the product is removed from this job's planned materials. If product already planned to be used and $quantity is different, it will overwrite previous value.
	**/
	public function set_used_material_assessment(object $product, $quantity, $unit = null)
	{
	}

	public function get_material_expense_list()
	{
		$ol = new object_list(array(
			"class_id" => CL_MATERIAL_EXPENSE,
			"lang_id" => array(),
			"site_id" => array(),
			"job" => $this->id()
		));
		$rv = array();
		foreach($ol->arr() as $entry)
		{
			$rv[$entry->prop("product")] = $entry;
		}
		return $rv;
	}
}

?>
