<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/Attic/method.aw,v 1.1 2005/03/17 18:30:25 kristo Exp $
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
				classload("core/icons");
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
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "method_props":
				// there is only ONE slight problem with the method_props:
				// you can't check type=int and give errors
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
		$prp = $arr["obj_inst"]->prop("method");
		$ob = $arr["obj_inst"]->prop("method_object");
		if(empty($prp) || empty($ob))
		{
			return;
		}
		
		$orb_class = get_instance("orb");
		$id = get_class(get_instance($arr["obj_inst"]->prop("method_class")));
		$orb_defs = $orb_class->load_xml_orb_def($id);
		$needed_props = $orb_defs[$id][$arr["obj_inst"]->prop("method")];
		$args = array();
		//arr($needed_props);
		if(is_array($needed_props))
		{
			foreach($needed_props as $key => $value)
			{
				if($key == "caption" || $key == "function" || $key == "define")
				{
					// filtering out defined params and other stuff -- ahz
					continue;
				}
				if(is_array($value))
				{
					foreach($value as $vkey => $vvalue)
					{
						if(!in_array($vkey, $filt))
						{
							$args[$vkey][$key] = $vvalue;
						}
					}
				}
			}
			$filt = array();
			$prp = $arr["obj_inst"]->prop("method_props");
			foreach($args as $key => $value)
			{
				$opts = array(
						0 => "-- vali --",
						1 => "saan päringust",
						2 => "määran käsitsi",
						3 => "objekti ID",
				);
				$rv = array();
				if($value["optional"])
				{
					$rv[] = "optional";
					$opts[4] = "ignoreerin";
				}
				elseif($value["required"])
				{
					$rv[] = "required";
				}
				if($value["types"])
				{
					$rv[] = "type=".$value["types"];
				}
				if($value["acl"])
				{
					$rv[] = "acl=".$value["acl"];
				}
				$asd = implode(", ", $rv);
				$filt[$key] = array(
					"name" => "method_props[$key][prop]",
					"caption" => $key.(!empty($asd) ? " ($asd)" : ""),
					"type" => "select",
					"options" => $opts,
					"selected" => $prp[$key]["prop"],
				);
				if($prp[$key]["prop"] == 2)
				{
					
					$filt[$key."value"] = array(
						"name" => "method_props[$key][value]",
						"caption" => "Väärtus",
						"type" => "textbox",
						"value" => $prp[$key]["value"],
					);
				} 
			}
		}
		//arr($args);
		//arr($needed_props);
		return $filt;
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
		@attrib name=method_parser is_public="1" caption="Meetodi kuvaja" all_args=1 default=1
		@param mid required type=int acl=view
	**/
	function method_parser($arr)
	{
		//arr($arr);
		$obj_inst = obj($arr["mid"]);
		//arr($obj_inst->properties());
		$prp = $obj_inst->prop("method_props");
		
		$classes = aw_ini_get("classes");
		list($obj, $name) = explode("/", $classes[$obj_inst->prop("method_class")]["file"]);
		
		$params = array();
		if(is_array($prp))
		{
			foreach($prp as $key => $value)
			{
				switch($value["prop"])
				{
					case 1:
						$params[$key] = $arr[$key];
						break;
					case 2:
						$params[$key] = $value["value"];
						break;
					case 3:
						$params[$key] = $arr["mid"];
						break;
				}
			}
		}
		//arr($params);
		//arr($obj_inst->prop("method"));
		return $this->do_orb_method_call(array(
			"action" => $obj_inst->prop("method"),
			"params" => $params,
			"class" => $name,
		));
	}
}
?>
