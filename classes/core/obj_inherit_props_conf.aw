<?php
// $Header: /home/cvs/automatweb_dev/classes/core/obj_inherit_props_conf.aw,v 1.1 2004/09/20 13:03:05 kristo Exp $
// obj_inherit_props_conf.aw - Objekti omaduste p&auml;rimine 
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_OBJ_INHERIT_PROPS_CONF, on_save_conf)


@classinfo syslog_type=ST_OBJ_INHERIT_PROPS_CONF relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property inherit_from type=relpicker reltype=RELTYPE_INHERIT_FROM
@caption Vali objekt, millelt p&auml;rida

@property inherit_from_prop type=select 
@caption Vali omadus, mida p&auml;rida

@property inherit_to_class type=select
@caption Vali klass, kuhu omadusi kirjutatakse

@property inherit_to_prop type=select
@caption Vali omadus, kuhu omadusi kirjutatakse

@reltype INHERIT_FROM value=1  clid=CL_MENU
@caption p&auml;ritav objekt

*/

class obj_inherit_props_conf extends class_base
{
	function obj_inherit_props_conf()
	{
		$this->init(array(
			"tpldir" => "core/obj_inherit_props_conf",
			"clid" => CL_OBJ_INHERIT_PROPS_CONF
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "inherit_from_prop":
				if (!is_oid($arr["obj_inst"]->prop("inherit_from")))
				{
					return PROP_IGNORE;
				}
				$ifo = obj($arr["obj_inst"]->prop("inherit_from"));
				$cu = get_instance("cfg/cfgutils");
				$props = $cu->load_properties(array(
					"clid" => $ifo->class_id()
				));
				
				$prop["options"] = array();
				foreach($props as $pn => $pd)
				{
					$prop["options"][$pn] = $pd["caption"];
				}
				break;

			case "inherit_to_class":
				$prop["options"] = get_class_picker();
				break;

			case "inherit_to_prop":
				if (!is_class_id($arr["obj_inst"]->prop("inherit_to_class")))
				{
					return PROP_IGNORE;
				}
				
				$cu = get_instance("cfg/cfgutils");
				$props = $cu->load_properties(array(
					"clid" => $arr["obj_inst"]->prop("inherit_to_class")
				));
				
				$prop["options"] = array();
				foreach($props as $pn => $pd)
				{
					$prop["options"][$pn] = $pd["caption"];
				}
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	

	function on_save_conf($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_OBJ_INHERIT_PROPS_CONF,
			"lang_id" => array(),
			"site_id" => array()
		));

		$data = array();
		foreach($ol->arr() as $o)
		{
			if ($o->prop("inherit_from") && $o->prop("inherit_from_prop") && $o->prop("inherit_to_class") && $o->prop("inherit_to_prop"))
			{
				$data[$o->prop("inherit_from")] = array(
					"from_prop" => $o->prop("inherit_from_prop"),
					"to_class" => $o->prop("inherit_to_class"),
					"to_prop" => $o->prop("inherit_to_prop")
				);
			}
		}

		$this->put_file(array(
			"file" => aw_ini_get("site_basedir")."/files/obj_inherit_props.conf",
			"content" => aw_serialize($data)
		));
	}
}
?>
