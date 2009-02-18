<?php

class material_movement_relation_obj extends _int_object
{
	function update_dn_rows($o, $data)
	{
		$dn = $o->prop("dn");
		if($dn && $this->can("view", $dn))
		{
			$dno = obj($dn);
			$conn = $dno->connections_from(array(
				"reltype" => "RELTYPE_ROW",
			));
			$name = $dno->name();
			foreach($conn as $c)
			{
				$row = $c->to();
				$prod = $row->prop("product");
				if($data[$prod])
				{
					$set_prods[$prod] = $prod;
					$row->set_prop("amount", $data[$prod]["amount"]);
					$row->set_prop("unit", $data[$prod]["unit"]);
				}
				else
				{
					$row->delete();
				}	
			}
			foreach($data as $prod => $d)
			{
				if(!$set_prods[$prod])
				{
					$this->create_dn_row(array(
						"dno" => $dno,
						"d" => $d,
						"name" => $name,
						"prod" => $prod,
					));
				}
			}
		}
	}

	function create_dn($o, $data)
	{
		$dno = obj();
		$dno->set_class_id(CL_SHOP_DELIVERY_NOTE);
		$dno->set_parent($o->parent());
		$name = sprintf(t("%s saateleht"), $o->prop("job.name"));
		$dno->set_name($name);
		$job = $o->prop("job");
		if($this->can("view", $job))
		{
			$case = obj($job)->get_first_obj_by_reltype("RELTYPE_MRP_PROJECT");
		}
		if($case)
		{
			$dno->set_prop("number", $case->name());
			$wh = $case->prop("warehouse");
		}
		$dno->set_prop("from_warehouse", $wh);
		$dno->save();
		foreach($data as $prod => $d)
		{
			$this->create_dn_row(array(
				"dno" => $dno,
				"d" => $d,
				"name" => $name,
				"prod" => $prod,
			));
		}
		$o->set_prop("dn", $dno->id());
	}

	function create_dn_row($arr)
	{
		extract($arr);
		$o = obj();
		$o->set_class_id(CL_SHOP_DELIVERY_NOTE_ROW);
		$o->set_parent($dno->id());
		$o->set_name(sprintf(t("%s rida"), $name));
		$o->set_prop("amount", $d["amount"]);
		$o->set_prop("unit", $d["unit"]);
		$o->set_prop("product", $prod);
		$o->save();
		$dno->connect(array(
			"to" => $o,
			"reltype" => "RELTYPE_ROW",
		));
	}
}

?>
