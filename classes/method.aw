<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/method.aw,v 1.2 2004/09/09 11:08:58 ahti Exp $
// method.aw - Klassi meetod
/*

@classinfo syslog_type=ST_METHOD relationmgr=yes

@default table=objects
@default field=meta
@default method=serialize
@default group=general

	@property method type=select
	@caption Meetod

	@property name type=text editonly=1
	@caption Meetodi nimi

	@property method_class_name type=text editonly=1
	@caption Klass

	@property comment type=textbox editonly=1
	@caption Kirjeldus

	@property method_class_id type=hidden editonly=1

*/

class method extends class_base
{
	function method()
	{
		$this->init(array(
			"tpldir" => "method",
			"clid" => CL_METHOD
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "method":
				$prop["options"] = $this->method_list ();
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		switch($prop["name"])
		{
			case "method":
				$methods = $this->method_list ();
				$this_aw_object->set_name ($methods [$prop["value"]]);
				break;
		}

		return $retval;
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function method_list()
	{
		$orb = get_instance("orb");
		$orb_defs = $orb->load_xml_orb_def(7);
		arr ($orb_defs);
		return array("0" => "--vali--") + $orb->get_classes_by_interface(array("interface" => "public"));
	}
	
	/**
		@attrib name=method_parser is_public="1" caption="Meetodi kuvaja"
			
	**/
	function method_parser($arr)
	{
		return null;
	}
}
?>
