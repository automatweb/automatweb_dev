<?php
// $Header: /home/cvs/automatweb_dev/classes/calendar/Attic/cal_content.aw,v 1.7 2005/03/20 15:39:17 kristo Exp $
/*

	@classinfo syslog_type=ST_CAL_CONTENT

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
				$orb = get_instance("core/orb/orb");
				$data["options"] = $orb->get_classes_by_interface(array("interface" => "content"));
				break;

			case "preview":
				$data["value"] = html::href(array(
					"caption" => t("Eelvaade"),
					"url" => $this->mk_my_orb("view",array("id" => $args["obj_inst"]->id())),
					"target" => "_blank",
				));
				break;
		};
		return $retval;
	}

	/**  
		
		@attrib name=view params=name default="0"
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
	function view($args = array())
	{
		$obj = new object($args["id"]);
		$this->mk_path($obj->parent(),t("/ Vaata"));
		$meta = $obj->meta();
		if (isset($meta["show_class"]))
		{
			list($pf,$pm) = explode("/",$meta["show_class"]);
			/// XXX: I need to check whether that class really has
			// content interface and whether that method really is a
			// content provider -- duke
			$retval = $this->do_orb_method_call(array(
				"class" => $pf,
				"action" => $pm,
				"params" => array(
					"count" => $meta["show_count"],
				),
			));
		}
		else
		{
			$retval = t("Klass on valimata, midagi pole näidata");
		};
		return $retval;
	}
}
?>
