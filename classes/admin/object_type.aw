<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/object_type.aw,v 1.15 2004/12/01 13:21:57 kristo Exp $
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
				$arr["obj_inst"]->set_meta("clf_type",$arr["request"]["clf_type"]);
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
					elseif ($o->id() == $data["value"] && !$o->flag(OBJ_FLAG_IS_SELECTED))
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
			if($arr["general"] === true)
			{
				$rv = $o->id();
			}
			else
			{
				$flg = $o->flag(OBJ_FLAG_IS_SELECTED);
				if ($o->flag(OBJ_FLAG_IS_SELECTED))
				{
					$rv = $o->id();
				};
			}
		};
		return $rv;
	}

	function gen_config($arr)
	{
		$obj = $arr["obj_inst"];
		$type = $obj->prop("type");

		$conns = $obj->connections_from(array(
			"type" => RELTYPE_META_ELEMENTS,
		));

		$opts = array("" => "");
		foreach($conns as $item)
		{
			$opts[$item->prop("to")] = $item->prop("to.name");
		};

		$mx = $obj->meta("classificator");
		$ct = $obj->meta("clf_type");


		$prop = $arr["prop"];

		// I need a new method -- get_properties_by_type
		// class_base fxt thingie needs it too to retrieve only the toolbar
		$defaults = $this->get_properties_by_type(array(
			"clid" => $obj->prop("type"),
			"type" => "classificator",
		));

		$types = array(
			"" => t("-vali-"),
			"mselect" => t("multiple select"),
			"select" => t("select"),
			"checkboxes" => t("checkboxid"),
			"radiobuttons" => t("radiobuttons"),
		);

		$rv = array();
		foreach($defaults as $key => $val)
		{
			$rv["c".$key] = array(
				"name" => "c".$key,
				"type" => "text",
				"caption" => $key,
			);

			$rv[$key] = array(
				"name" => "classificator[" . $key . "]",
				"selected" => $mx[$key],
				"type" => "select",
				"caption" => t("Oks"),
				"options" => $opts,
				"parent" => "c".$key,
			);

			$rv["x".$key] = array(
				"name" => "clf_type[" . $key . "]",
				"type" => "select",
				"caption" => t("Tüüp"),
				"options" => $types,
				"selected" => $ct[$key],
				"parent" => "c".$key,
			);
		};

		return $rv;
	}

	function get_type_picker()
	{
		$ret = array();
		$tmp = aw_ini_get("classes");
		foreach($tmp as $clid => $cldat)
		{
			//if ($cldat["can_add"] == 1)
			//{
				$ret[$clid] = $cldat["name"];
			//}
		}
		asort($ret);
		$ret = array("__all_objs" => t("K&otilde;ik")) + $ret;
		return $ret;
	}

	////
	// !builds the url for adding a new object
	function get_add_url($arr)
	{
		$o = new object($arr["id"]);

		$tmp = aw_ini_get("classes");
		$clss = $tmp[$o->prop("type")]["file"];
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

	/** reads the properties from the object type $o and returns them. honors cfgforms
	**/
	function get_properties($o)
	{
		// get a list of properties in both classes
		$cfgx = get_instance("cfg/cfgutils");
		$ret = $cfgx->load_properties(array(
			"clid" => $o->subclass(),
		));

		if ($o->prop("use_cfgform"))
		{
			$class_i = get_instance($o->subclass() == CL_DOCUMENT ? "doc" : $o->subclass());
			$tmp = $class_i->load_from_storage(array(
				"id" => $o->prop("use_cfgform")
			));

			$dat = array();
			foreach($tmp as $pn => $pd)
			{
				$dat[$pn] = $ret[$pn];
				$dat[$pn]["caption"] = $pd["caption"];
			}
			$ret = $dat;
		}

		return $ret;
	}
}
?>
