<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/method.aw,v 1.3 2004/09/13 14:21:54 ahti Exp $
// method.aw - Klassi meetod
/*

@classinfo syslog_type=ST_METHOD relationmgr=yes

@default table=objects
@default field=meta
@default method=serialize
@default group=general

@property method_class type=select
@caption Klass

@property method_ctype type=text editonly=1 field=method_class
@caption Tüüp

@property method_object type=select
@caption Objekt

@property method type=select
@caption Meetod

@property method_props type=callback callback=callback_method_props no_caption=1
@caption Meetodi jaoks vajalikud property'd

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
		
		$prp = $arr["obj_inst"]->prop("method_class");
		$ob = $arr["obj_inst"]->prop("method_object");
		switch($prop["name"])
		{
			case "method":
				$obj = obj($ob);
				if(empty($prp) || empty($ob) || $obj->class_id() != $prp)
				{
					return PROP_IGNORE;
				}
				$inst = get_instance($prp);
				$classes = aw_ini_get("classes");
				$prop["options"] = $this->method_list(array(
					"flag" => "function",
					"id" => get_class($inst),
					"name" => $classes[$prp]["name"],
				));
				break;
				
			case "method_class":
				$cx = get_instance("cfg/cfgutils");
				$class_list = $cx->get_classes_with_properties();
				foreach($class_list as $key => $val)
				{
					$prop["options"][$key] = $val;
				};
				break;
				
			case "method_ctype":
				if(empty($prp))
				{
					return PROP_IGNORE;
				}
				classload("icons");
				$iu = html::img(array(
					"url" => icons::get_icon_url($prp,""),
				));
				$tmp = aw_ini_get("classes");
				$prop["value"] = $iu . " " . $tmp[$prp]["name"];
				break;

			case "method_object":
				if(empty($prp))
				{
					return PROP_IGNORE;
				}
				$objects = new object_list(array(
					"class_id" => $prp,
				));
				$arr = $objects->arr();
				if(empty($arr))
				{
					return PROP_IGNORE;
				}
				foreach($arr as $id => $object)
				{
					$rval[$id] = $object->prop("name");
				}
				$prop["options"] = array(0 => "-- vali --") + $rval;
				break;
			
			case "method_props":
				$prp = $arr["obj_inst"]->prop("method");
				if(empty($prp))
				{
					return PROP_IGNORE;
				}
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		//$this_object =& $arr["obj_inst"];

		switch($prop["name"])
		{
			case "method":
				//$methods = $this->method_list();
				//$this_aw_object->set_name($methods [$prop["value"]]);
				break;
			case "method_class":
				/*
				// do not overwrite subclass if it was not in the form
				// hum .. this is temporary fix of course. yees --duke
				if (empty($arr["request"]["subclass"]))
				{
					$retval = PROP_IGNORE;
				}
				// cfg_proplist is in "formdata" only if this a serialized object
				// being unserialized. for example, if we are copying this object
				// over xml-rpc
				elseif ($arr["new"] && empty($arr["request"]["cfg_proplist"]))
				{
					// fool around a bit to get the correct data
					$subclass = $arr["request"]["subclass"];

					// now that's the tricky part ... this thingsbum overrides
					// all the settings in the document config form
					$this->_init_properties($subclass);
					$cfgu = get_instance("cfg/cfgutils");
					if ($subclass == CL_DOCUMENT)
					{
						$def = join("",file(aw_ini_get("basedir") . "/xml/documents/def_cfgform.xml"));
						list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $def));
						$this->cfg_proplist = $proplist;
						$this->cfg_groups = $grplist;
					}
					else
					{
						$tmp = aw_ini_get("classes");
						$fname = $tmp[$subclass]["file"];
						$def = join("",file(aw_ini_get("basedir") . "/xml/properties/class_base.xml"));
						list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $def));
						$this->cfg_proplist = $proplist;
						$this->cfg_groups = $grplist;
						$fname = basename($fname);
						$def = join("",file(aw_ini_get("basedir") . "/xml/properties/$fname.xml"));
						list($proplist,$grplist) = $cfgu->parse_cfgform(array("xml_definition" => $def));
						// nono. It needs to fucking merge those things with classbase 
						$this->cfg_proplist = $this->cfg_proplist + $proplist;
						$this->cfg_groups = $this->cfg_groups + $grplist;


					};
				};
				*/
				break;
		}

		return $retval;
	}
	function method_list($arr)
	{
		extract($arr);
		$orb = get_instance("orb");
		return array("0" => "--vali--") + $orb->get_methods_by_flag(array(
			"flag" => $flag,
			"id" => $id,
			"name" => $name,
			"no_id" => true,
		));
	}
	
	function callback_method_props($arr)
	{
		
		$orb_class = get_instance("orb");
		$id = get_class(get_instance($arr["obj_inst"]->prop("method_class")));
		$orb_defs = $orb_class->load_xml_orb_def($id);
		$needed_props = $orb_defs[$id][$arr["obj_inst"]->prop("method")];
		$args = array();
		foreach($needed_props as $key => $value)
		{
			if($key == "caption" || $key == "function")
			{
				continue;
			}
			foreach($value as $vkey => $vvalue)
			{
				$args[$vkey][$key] = $vvalue;
			}
		}
		//arr($args);
		//arr($needed_props);
		return $args;
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
	
	/**
		@attrib name=method_parser is_public="1" caption="Meetodi kuvaja"
		@param id required type=int acl=view
	**/
	function method_parser($arr)
	{
		//
		arr($arr);
		/*
		$obj_inst = obj($arr["id"]);
		$this->do_orb_method_call(array(
			"action" => $obj_inst->prop("method"),
			"params" => array(
				"id" => $obj_inst->prop("method_object"),
			),
			"class" => $obj_inst->prop("method_class"),
		));
		*/
		return null;
	}
}
?>
