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

	function callback_get_contents($args = array())
	{
		$relobj = $this->get_object(array(
			"oid" => $args["obj"]["oid"],
			"clid" => $this->clid,
		));
		
		$cldef = $this->cfg["classes"][$relobj["subclass"]];
		$values = $relobj["meta"]["values"][$cldef["def"]];

		$cfgu = get_instance("cfg/cfgutils");
		$rel_properties = $cfgu->load_class_properties(array(
			"clid" => $relobj["subclass"],
			"filter" => "rel",
		));

		// cause get_instance does not work with subfolders
		// hell yes it does! - terryf
		$clname = $cldef["file"];
		$t = get_instance($clname);

		$t->init_class_base();

		$t->values = $values;

		$resprops = $t->parse_properties(array(
			"properties" => &$rel_properties,
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

					$cldef = $this->cfg["classes"][$relobj["subclass"]];
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


	////////////////////////////////////
	// object persistance functions - used when copying/pasting object
	// if the object does not support copy/paste, don't define these functions
	////////////////////////////////////

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

}
?>
