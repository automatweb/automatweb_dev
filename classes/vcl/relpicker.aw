<?php
class relpicker extends  core
{
	function relpicker()
	{
		$this->init("");
	}

	function init_vcl_property($arr)
	{
		$prop = &$arr["property"];
		$this->obj = $arr["obj_inst"];
		$val = &$arr["property"];

                $options = array("0" => "--vali--");
                // generate option list
                if (defined($prop["reltype"]) && constant($prop["reltype"]))
                {
                        $reltype = constant($prop["reltype"]);
                }
                else
                {
                        $reltype = $prop["reltype"];
                };

		if (is_array($prop["options"]))
		{
                        $val["type"] = "select";
                        $val["options"] = $prop["options"];

		}
		else
                // if automatic is set, then create a list of all properties of that type
                if (isset($prop["automatic"]))
                {
			$clid = $arr["relinfo"][$reltype]["clid"];
                        $val["type"] = "select";
			if (!empty($clid))
			{
				$olist = new object_list(array(
					"class_id" => $clid,
				));

				$names = $olist->names();
				asort($names);
                        
				$val["options"] = $options + $names;
			};
                }
                else
                if ($arr["id"])
                {
                        $o = obj($arr["id"]);
                        $conn = $o->connections_from(array(
                                "type" => $reltype
                        ));

                        foreach($conn as $c)
                        {
                                $options[$c->prop("to")] = $c->prop("to.name");
                        }

                        $val["type"] = "select";
                        $val["options"] = $options;
                }
		return array($val["name"] => $val);

	}

	function process_vcl_property($arr)
	{
		$property = $arr["prop"];

		if ($property["type"] == "relpicker" && $property["automatic"] == 1)
		{
			$obj_inst = $arr["obj_inst"];
			$conns = array();
			if (!$arr["new"])
			{
				$rt = $arr["relinfo"][$property["reltype"]]["value"];
				$conns = $obj_inst->connections_from(array(
					"type" => $rt,
				));
			};

			// no existing connection, create a new one
			if ($arr["new"] || sizeof($conns) == 0)
			{
				if ($property["value"] != 0)
				{
					$obj_inst->connect(array(
						"to" => $property["value"],
						"reltype" => $rt,
					));
				};
			}
			else
			{
				// alter existing connection
				list(,$existing) = each($conns);
				if ($property["value"] == 0)
				{
					$existing->delete();
				}
				else
				{
					$existing->change(array(
						"to" => $property["value"],
					));
				};
			};

		};

	}
};
?>
