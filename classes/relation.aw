<?php

/*

@classinfo syslog_type=ST_RELATION

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property contents type=callback callback=callback_get_contents store=no
@caption Sisu

*/

class relation extends class_base
{
	function relation()
	{
		$this->init(array(
			"clid" => CL_RELATION
		));
	}

	// well, this really is deprecated .. meaning that I don't do it this way anymore
	function callback_get_contents($args = array())
	{
		$relobj = $this->get_object(array(
			"oid" => $args["obj"]["oid"],
			"clid" => $this->clid,
		));

		if (empty($relobj["subclass"]))
		{
			die("this relation object has no subclass, please run converters->convert_aliases()");
		};
		
		$reldata = $this->db_fetch_row("SELECT target FROM aliases WHERE relobj_id = '$relobj[oid]'");

		$tmp = aw_ini_get("classes");
		$cldef = $tmp[$relobj["subclass"]];
		$values = $relobj["meta"]["values"][$cldef["def"]];

		$cfgu = get_instance("cfg/cfgutils");


		$t = get_instance($cldef["file"]);

		$t->init_class_base();

		$t->values = $values;

		$resprops = $t->parse_properties(array(
			"properties" => &$rel_properties,
			"target_obj" => $reldata["target"],
		));

		return $resprops;
	}

	// now I have an interesting dilemma, I have to show a different configuration form
	// based on the subclass of the relation object. This means I need to be able to 
	// go between the load object data of class_base

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "comment":
			case "status":
				$retval = PROP_IGNORE;
				break;
		};
		return $retval;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "status":
				$data["value"] = STAT_ACTIVE;
				break;

			case "contents":
				$relobj = $this->get_object(array(
					"oid" => $args["obj"]["oid"],
					"clid" => $this->clid,
				));

				$cfgu = get_instance("cfg/cfgutils");
				$rel_properties = $cfgu->load_class_properties(array(
					"clid" => $relobj["subclass"],
					"filter" => "rel",
				));

				$set_vals = array();

				if (sizeof($rel_properties) > 0)
				{
					foreach($rel_properties as $key => $val)
					{
						$set_vals[$key] = $args["form_data"][$key];
					};

					$tmp = aw_ini_get("classes");
					$cldef = $tmp[$relobj["subclass"]];
				};		

				if (sizeof($set_vals) > 0)
				{
					$meta = &$args["metadata"];
					$meta["values"][$cldef["def"]] = $set_vals;
				};		
				break;
		}
		return $retval;
	}

}
?>
