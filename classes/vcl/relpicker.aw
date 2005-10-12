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
		$reltype = $prop["reltype"];
		// generate option list
		if (is_array($prop["options"]))
		{
			$val["type"] = "select";
			$val["options"] = $prop["options"];
		}
		else
		{
		// if automatic is set, then create a list of all properties of that type
			if (isset($prop["automatic"]))
			{
				$clid = $arr["relinfo"][$reltype]["clid"];
				$val["type"] = "select";
				if (!empty($clid))
				{
					$olist = new object_list(array(
						"class_id" => $clid,
						"site_id" => array(),
						"lang_id" => array()
					));
					$names = $olist->names();
					asort($names);            
					$val["options"] = $options + $names;
					if ($arr["id"])
					{
						$o = obj($arr["id"]);
						$conn = $o->connections_from(array(
							"type" => $reltype
						));
						$sel = array();
						foreach($conn as $c)
						{
							$sel[$c->prop("to")] = $c->prop("to");
						}
						$val["value"] = $sel;
					};
				};
			}
			else
			{
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
					$val["options"] = $options;
				};
			}
		}
		$val["type"] = ($val["display"] == "radio") ? "chooser" : "select";
		if ($val["type"] == "select" && is_object($this->obj) && is_oid($this->obj->prop($val["name"])))
		{
			$val["post_append_text"] = " ".html::get_change_url($this->obj->prop($val["name"]), array("return_url" => get_ru()), t("Muuda valitud objekti"));
		}
		if ($val["type"] == "select" && is_object($this->obj) && is_oid($this->obj->id()) && aw_global_get("uid") == "kix")
		{
			$clid = $arr["relinfo"][$reltype]["clid"];
			$rel_val = $arr["relinfo"][$reltype]["value"];
			if (is_array($clid))
			{
				$clid = reset($clid);
			}

			if (is_class_id($clid))
			{
				$val["post_append_text"] .= " / ".html::get_new_url(
					$clid, 
					$this->obj->id(), 
					array(
						"alias_to" => $this->obj->id(), 
						"reltype" => $rel_val,
						"return_url" => get_ru()
					), 
					t("Lisa uus objekt")
				);
			}
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
			$rt = $arr["relinfo"][$property["reltype"]]["value"];
			if (!$arr["new"])
			{
				$rt = $arr["relinfo"][$property["reltype"]]["value"];
				$conns = $obj_inst->connections_from(array(
					"type" => $property["reltype"],
				));
			};
			// no existing connection, create a new one
			if ($arr["new"] || sizeof($conns) == 0)
			{
				if (is_array($property["value"]))
				{
					foreach($property["value"] as $pval)
					{
						$obj_inst->connect(array(
							"to" => $pval,
							"reltype" => $rt,
						));
					}
				}
				else
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
				if (is_array($property["value"]))
				{
					foreach($conns as $conn)
					{
						if (!in_array($conn->prop("to"),$property["value"]))
						{

							$conn->delete();
						};
					}
				}
				else
				{
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
		};
	}
};
?>
