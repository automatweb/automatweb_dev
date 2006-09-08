<?php

class quick_add extends class_base
{
	function quick_add()
	{
	}

	function init_vcl_property($arr)
	{
		// read props from the given class
		$prop = $arr["prop"];
	
		$tmp = obj();
		$tmp->set_class_id(@constant($prop["clid"]));
		$pl = $tmp->get_property_list();

		$ret = array();
		foreach($prop["props"] as $p)
		{
			$pn = $prop["name"]."[".$p."]";
			$ret[$pn] = $pl[$p];
			$ret[$pn]["parent"] = $prop["parent"];
			$ret[$pn]["name"] = $pn;
			$ret[$pn]["size"] = 18;
			$ret[$pn]["captionside"] = "top";
			$ret[$pn]["store"] = "no";
			$ret[$pn]["value"] = "";
		}
		$ret[$prop["name"]."[sbt]"] = array(
			"name" => $prop["name"]."[sbt]",
			"type" => "submit",
			"caption" => t("Lisa"),
			"parent" => $prop["parent"],
			"no_caption" => 1,
			"store" => "no"
		);
		$ret[$prop["name"]."[sbtm]"] = array(
			"name" => $prop["name"]."[sbtm]",
			"type" => "submit",
			"caption" => t("Lisa ja muuda"),
			"parent" => $prop["parent"],
			"no_caption" => 1,
			"store" => "no"
		);
		return $ret;
	}

	function process_vcl_property($arr)
	{
		// if any of the fields are filled, then do the add thingie
		$prop = $arr["prop"];
		$add = false;
		foreach($prop["props"] as $p)
		{
			if ($prop["value"][$p] != "")
			{
				$add = true;
			}
		}

		if ($add)
		{
			$o = obj();
			$o->set_class_id(@constant($prop["clid"]));
			$o->set_parent($arr["obj_inst"]->id());
			foreach($prop["props"] as $p)
			{
				$o->set_prop($p, $prop["value"][$p]);
			}
			$o->save();

			if ($prop["value"]["sbtm"] != "")
			{
				header("Location: ".$this->mk_my_orb("change", array("id" => $o->id(), "return_url" => $arr["request"]["post_ru"]), $o->class_id()));
				die();
			}

			header("Location: ".$arr["request"]["post_ru"]);
			die();
		}
	}
}
?>
