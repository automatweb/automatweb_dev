<?php
// $Header: /home/cvs/automatweb_dev/classes/translate/Attic/class_translation.aw,v 1.4 2004/10/27 12:04:30 kristo Exp $
// translation.aw - Tõlge 
/*

@classinfo syslog_type=ST_TRANSLATION

@default table=objects
@default group=general

@property lang_code type=select field=meta method=serialize
@caption Keel

@property subclass type=select newonly=1
@caption Klass

@property info type=text editonly=1 store=no group=general,workbench
@caption Info 

@property preview type=callback callback=callback_get_preview_links editonly=1 store=no group=workbench
@caption Eelvaade

@property workbench type=callback callback=callback_gen_workbench store=no group=workbench no_caption=1
@caption Tõlkimine

@groupinfo workbench caption="Tõlgi"
@classinfo relationmgr=yes

*/

// tõlke testimiseks
define("RELTYPE_TEST",1);

class class_translation extends class_base
{
	function class_translation()
	{
		$this->init(array(
			"tpldir" => "translate",
			"clid" => CL_TRANSLATION
		));
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_TEST => "tõlke test",
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		$retval = false;
		switch($args["reltype"])
		{
                        case RELTYPE_TEST:
				// I need to fill this array dynamically. augh!
				// and I do not think I can do it right now --duke
				//$retval = array(CL_FILE);
				break;
		};
		return $retval;
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "subclass":
				$cfgu = get_instance("cfg/cfgutils");
				$cxlist = $cfgu->get_classes_with_properties();
				asort($cxlist);
				$data["options"] = $cxlist;
				break;

			case "lang_code":
				$tmp = aw_ini_get("languages.list");
				$lang_codes = array();
			
				foreach($tmp as $langdata)
				{
					$lang_codes[$langdata["acceptlang"]] = $langdata["acceptlang"] . " (" . $langdata["name"] . ")";
				};
				$data["options"] = $lang_codes;
				break;

			case "info":
				$langs = aw_ini_get("languages.list");
				$clss = aw_ini_get("classes");
				$data["value"] = "Keel: " . $args["obj_inst"]->prop("lang_code"). "<br>" . "Klass: " . $clss[$args["obj_inst"]->subclass()]["name"];
				break;

		}
		return $retval;
	}

	function callback_get_preview_links($arr)
	{
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => RELTYPE_TEST,
		));

		$retval = array();

		$clss = aw_ini_get("classes");
		foreach($conns as $id => $item)
		{
			$target_obj = $item->to();
			$classinf = $clss[$target_obj->class_id()];
			$retval["prev" . $id] = array(
				"type" => "text",
				"caption" => $classinf["name"] . " eelvaade",
				"value" => html::href(array(
					"url" => $this->mk_my_orb("change",array(
							"id" => $target_obj->id(),
							"trid" => $arr["obj_inst"]->id(),
						),basename($classinf["file"])),
					"caption" => $target_obj->name(),
				)),

			);	
		}

		return $retval;
	}

	function callback_pre_edit($arr)
	{
	
		$subclass = $arr["obj_inst"]->prop("subclass");

		// let us just read the fucking properties and be done with it
		$cfgu = get_instance("cfg/cfgutils");
		$props = $cfgu->load_properties(array(
			"clid" => $subclass,
		));

		$groupinfo = $cfgu->groupinfo;

		$this->dt = array();
		foreach($props as $key => $val)
		{
			// this way it does not change if a class file is renamed
			$id = md5("prop" . $subclass . $key);
			$this->dt[$id] = array(
				"ctx" => $val["type"],
				"caption" => $val["caption"],
				"comment" => $val["comment"],
			);
				
		}

		$clss = aw_ini_get("classes");
		$classname = $clss[$subclass]["name"];

		foreach($groupinfo as $key => $val)
		{
			$id = md5("group" . $subclass . $key);
			$this->dt[$id] = array(
				"ctx" => "tab",
				"caption" => $val["caption"],
				"comment" => $val["comment"],
			);
		};
		
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => RELTYPE_TEST,
			"class" => $subclass,
		));

		// if there isn't one yet, create a test object and connect it to the current one
		if (sizeof($conns) == 0)
		{
			$parent = $arr["obj_inst"]->parent();
			$o = new object(array(
				"name" => "test $name", 
				"parent" => $parent,
				"class_id" => $subclass,
			));
			$o->save();

			$arr["obj_inst"]->connect(array(
				"from" => $arr["obj_inst"]->id(),
				"to" => $o->id(),
				"reltype" => RELTYPE_TEST,
			));
		}

	}

	function callback_gen_workbench($arr)
	{
		// first, load the correct translation file
		$this->read_template("table.tpl");
		$prop = $arr["prop"];
		$c = "";
		$cntr = 0;
		$trans = $arr["obj_inst"]->meta("trans");
		foreach($this->dt as $key => $val)
		{
			$this->vars(array(
				"color" => $cntr % 2 ? "#F0FDFF" : "#FDFFEA",
				"id" => $key,
				"context" => $val["ctx"],
				"cap_orig" => $val["caption"],
				"comm_orig" => $val["comment"],
				"file" => $val["file"],
				"cap_trans" => $trans[$key]["caption"],
				"comm_trans" => $trans[$key]["comment"],
			));
			$cntr++;
			$c .= $this->parse("ITEM");
		};
		$this->vars(array(
			"ITEM" => $c,
		));
		$prop["value"] = $this->parse();
		return array($prop);
	}

	function callback_pre_save($arr)
	{
		if (!empty($arr["request"]["trans"]))
		{
			$arr["obj_inst"]->set_meta("trans",$arr["request"]["trans"]);
		};
	}
}
?>
