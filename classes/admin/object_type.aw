<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/object_type.aw,v 1.5 2004/03/09 15:35:06 kristo Exp $
// object_type.aw - objekti klass (lisamise puu jaoks)
/*
	@default table=objects
	@default group=general

	@property type type=select field=subclass
	@caption Objektitüüp
	
	@default field=meta
	@default method=serialize

	@property use_cfgform type=relpicker reltype=RELTYPE_OBJECT_CFGFORM
	@caption Kasuta seadete vormi

	@property configuration type=callback callback=gen_config store=no group=settings
	@caption Klassi konfiguratsioon

	@property default_object type=chooser store=no group=defobj orient=vertical
	@caption Vaikimisi objekt

	@groupinfo settings caption="Klassi konfiguratsioon"
	@groupinfo defobj caption="Aktiivne objekt"

	@classinfo relationmgr=yes
	
	@reltype OBJECT_CFGFORM value=1 clid=CL_CFGFORM
	@caption Seadete vorm

	@reltype META_ELEMENTS value=2 clid=CL_META
	@caption Muutuja

*/

class object_type extends class_base
{
	function object_type()
	{
		$this->init(array(
			"clid" => CL_OBJECT_TYPE,
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "type":
				$data["options"] = $this->get_type_picker();
				$old_type = $arr["obj_inst"]->meta("type");
				if (!empty($old_type))
				{
					$data["selected"] = $old_type;
				};
				break;

			case "default_object":
				$ol = new object_list(array(
					"class_id" => CL_OBJECT_TYPE,
					"subclass" => $arr["obj_inst"]->prop("type"),
					"lang_id" => array(),
					"site_id" => array(),
				));
				$data["options"] = $ol->names();
                                for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
                                {
					$flg = $o->flag(OBJ_FLAG_IS_SELECTED);
					if ($o->flag(OBJ_FLAG_IS_SELECTED))
					{
						$data["value"] = $o->id();
					};
				};
				break;
		}
		return $retval;
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "configuration":
				$arr["obj_inst"]->set_meta("classificator",$arr["request"]["classificator"]);
				break;

			case "type":
				$old_type = $arr["obj_inst"]->meta("type");
				if (!empty($old_type))
				{
					$arr["obj_inst"]->set_meta("type","");
				};
				break;

			case "default_object":
				$ol = new object_list(array(
                                        "class_id" => $this->clid,
					"subclass" => $arr["obj_inst"]->prop("type"),
					"lang_id" => array(),
                                ));
				
                                for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
                                {
					if ($o->flag(OBJ_FLAG_IS_SELECTED) && $o->id() != $data["value"])
                                        {
                                                $o->set_flag(OBJ_FLAG_IS_SELECTED, false);
                                                $o->save();
                                        }
                                        else
                                        if ($o->id() == $data["value"] && !$o->flag(OBJ_FLAG_IS_SELECTED))
                                        {
                                                $o->set_flag(OBJ_FLAG_IS_SELECTED, true);
                                                $o->save();
					};
				};
				break;

		};
		return $retval;
	}

	function get_obj_for_class($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_OBJECT_TYPE,
			"subclass" => $arr["clid"],
			"lang_id" => array(),
		));
		$rv = false;
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$flg = $o->flag(OBJ_FLAG_IS_SELECTED);
			if ($o->flag(OBJ_FLAG_IS_SELECTED))
			{
				$rv = $o->id();
			};
		};
		return $rv;
	}

	function gen_config($arr)
	{
		$obj = $arr["obj_inst"];
		$type = $obj->prop("type");

		$conns = $obj->connections_from(array(
			"reltype" => RELTYPE_META_ELEMENTS,
		));

		$opts = array("" => "");
		foreach($conns as $item)
		{
			$opts[$item->prop("to")] = $item->prop("to.name");
		};

		$mx = $obj->meta("classificator");


		$prop = $arr["prop"];

		// I need a new method -- get_properties_by_type
		// class_base fxt thingie needs it too to retrieve only the toolbar

                $defaults = $this->get_properties_by_type(array(
                        "clid" => $obj->prop("type"),
                        "type" => "classificator",
                ));

		$rv = array();
		foreach($defaults as $key => $val)
		{
			$rv[$key] = array(
				"name" => "classificator[" . $key . "]",
				"selected" => $mx[$key],
				"type" => "select",
				"caption" => $key,
				"options" => $opts,
			);
		};

		return $rv;
	}

	function get_type_picker()
	{
		$ret = array();
		foreach($this->cfg["classes"] as $clid => $cldat)
		{
			if ($cldat["can_add"] == 1)
			{
				$ret[$clid] = $cldat["name"];
			}
		}
		asort($ret);
		$ret = array("__all_objs" => "K&otilde;ik") + $ret;
		return $ret;
	}

	////
	// !builds the url for adding a new object
	function get_add_url($arr)
	{
		$o = new object($arr["id"]);

		$clss = $this->cfg["classes"][$o->prop("type")]["file"];
		if ($clss == "document")
		{
			$clss = "doc";
		}
		$rv = $this->mk_my_orb("new", array(
				"parent" => $arr["parent"],
				"period" => aw_global_get("period"),
				"section" => $arr["section"],
				"cfgform" => $o->prop("use_cfgform"),
			 ),$clss);
		return $rv;
	}
}
?>
