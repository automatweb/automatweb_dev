<?php
// $Header: /home/cvs/automatweb_dev/classes/calendar/Attic/cal_content.aw,v 1.1 2003/03/19 18:05:27 duke Exp $
/*

	@classinfo syslog_type=ST_CAL_CONTENT

	@groupinfo general caption=Üldine

	@default table=objects
	@default group=general
	@default field=meta
	@default method=serialize

	@property show_class type=select 
	@caption Vali klass

	@property show_count type=textbox size=4
	@caption Mitu
	
	@property preview type=text editonly=1
	@caption Eelvaade

*/

class cal_content extends class_base
{
	function cal_content()
	{
		$this->init(array(
			'clid' => CL_CAL_CONTENT
		));
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
                $retval = PROP_OK;
                switch($data["name"])
                {
			case "show_class":
				$awo = get_instance("aw_orb");
				$data["options"] = $awo->get_classes_by_interface(array("interface" => "content"));
				break;

			case "preview":
				$data["value"] = html::href(array(
					"caption" => "Eelvaade",
					"url" => $this->mk_my_orb("view",array("id" => $args["obj"]["oid"])),
					"target" => "_blank",
				));
				break;


		};
		return $retval;
	}

	function view($args = array())
	{
		$obj = $this->get_object(array(
			"oid" => $args["id"],
			"class_id" => CL_CAL_CONTENT,
		));
		$this->mk_path($obj["parent"],"/ Vaata");
		if (isset($obj["meta"]["show_class"]))
		{
			list($pf,$pm) = explode("/",$obj["meta"]["show_class"]);
			$t = get_instance($pf);
			/// XXX: I need to check whether that class really has
			// content interface and whether that method really is a
			// content provider -- duke
			$retval = $this->do_orb_method_call(array(
				"class" => $pf,
				"action" => $pm,
				"params" => array(
					"count" => $obj["meta"]["show_count"],
				),
			));
		}
		else
		{
			$retval = "Klass on valimata, midagi pole näidata";
		};
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

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob['name']
		));

		return $this->parse();
	}
}
?>
